<?php

/**
 * Order Manipulation Handler
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Order Manipulation class
 */
class PExpress_Admin_Order_Manipulation
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize hooks
     */
    private function init()
    {
        // AJAX handlers
        add_action('wp_ajax_polar_add_order_item', array($this, 'ajax_add_order_item'));
        add_action('wp_ajax_polar_remove_order_item', array($this, 'ajax_remove_order_item'));
        add_action('wp_ajax_polar_update_order_item', array($this, 'ajax_update_order_item'));
        add_action('wp_ajax_polar_replace_order_item', array($this, 'ajax_replace_order_item'));
        add_action('wp_ajax_polar_get_product_alternatives', array($this, 'ajax_get_product_alternatives'));
        add_action('wp_ajax_polar_recalculate_order_totals', array($this, 'ajax_recalculate_order_totals'));
        add_action('wp_ajax_polar_search_products', array($this, 'ajax_search_products'));
        add_action('wp_ajax_polar_forward_order_to_hr', array($this, 'ajax_forward_order_to_hr'));
        add_action('wp_ajax_polar_revoke_order_from_hr', array($this, 'ajax_revoke_order_from_hr'));
        add_action('wp_ajax_polar_update_shipping_address', array($this, 'ajax_update_shipping_address'));

        // Allow WooCommerce product search for our users
        add_filter('woocommerce_json_search_found_products', array($this, 'allow_product_search'), 10, 1);

        // Enqueue assets on custom order edit page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        // Fallback to ensure CSS loads for all users
        add_action('admin_print_styles', array($this, 'enqueue_assets_fallback'));

        // Add body class to order edit page
        add_filter('admin_body_class', array($this, 'add_order_edit_body_class'));
    }

    /**
     * AJAX handler: Search products for select dropdown
     */
    public function ajax_search_products()
    {
        $current_user = wp_get_current_user();

        // PEXPRESS FIX: Allow polar_support to search
        $allowed = false;
        if (in_array('polar_support', $current_user->roles) || current_user_can('edit_shop_orders')) {
            $allowed = true;
        }

        if (!$allowed) {
            wp_send_json(array());
        }

        $nonce = isset($_REQUEST['security']) ? sanitize_text_field(wp_unslash($_REQUEST['security'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'search-products')) {
            wp_send_json(array());
        }

        $term = isset($_REQUEST['term']) ? wc_clean(wp_unslash($_REQUEST['term'])) : '';
        if ('' === $term) {
            wp_send_json(array());
        }

        $limit = isset($_REQUEST['limit']) ? max(absint($_REQUEST['limit']), 1) : absint(apply_filters('woocommerce_json_search_limit', 30));
        $include_ids = !empty($_REQUEST['include']) ? array_map('absint', (array) wp_unslash($_REQUEST['include'])) : array();
        $exclude_ids = !empty($_REQUEST['exclude']) ? array_map('absint', (array) wp_unslash($_REQUEST['exclude'])) : array();

        $exclude_types = array();
        if (!empty($_REQUEST['exclude_type'])) {
            $exclude_types = wp_unslash($_REQUEST['exclude_type']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            if (!is_array($exclude_types)) {
                $exclude_types = explode(',', $exclude_types);
            }

            foreach ($exclude_types as &$exclude_type) {
                $exclude_type = strtolower(trim($exclude_type));
            }
            $exclude_types = array_intersect(
                array_merge(array(\Automattic\WooCommerce\Enums\ProductType::VARIATION), array_keys(wc_get_product_types())),
                $exclude_types
            );
        }

        $data_store = WC_Data_Store::load('product');
        $ids = $data_store->search_products($term, '', true, false, $limit, $include_ids, $exclude_ids);

        $products = array();

        foreach ($ids as $product_id) {
            $product = wc_get_product($product_id);

            // PEXPRESS FIX: Remove the wc_products_array_filter_readable check for our support agents
            // or ensure they pass it. Since we can't easily modify the capability check inside that function,
            // we'll just skip it if we are a polar_support user, assuming we trust them.

            if (!$product || in_array($product->get_type(), $exclude_types, true)) {
                continue;
            }

            // Explicitly allow if polar_support, otherwise do standard check
            if (!in_array('polar_support', $current_user->roles) && !wc_products_array_filter_readable($product)) {
                continue;
            }

            $label = rawurldecode(wp_strip_all_tags($product->get_formatted_name()));

            if ($product->managing_stock() && !empty($_REQUEST['display_stock'])) {
                $stock_amount = $product->get_stock_quantity();
                /* translators: %d stock amount */
                $label .= ' - ' . sprintf(__('Stock: %d', 'woocommerce'), wc_format_stock_quantity_for_display($stock_amount, $product));
            }

            $products[$product_id] = $label;
        }

        $products = apply_filters('woocommerce_json_search_found_products', $products);

        $results = array();
        foreach ($products as $product_id => $label) {
            $results[] = array(
                'id' => $product_id,
                'text' => $label,
            );
        }

        wp_send_json_success($results);
    }

    /**
     * Allow product search for support users
     */
    public function allow_product_search($products)
    {
        $current_user = wp_get_current_user();
        if (in_array('polar_support', $current_user->roles) || current_user_can('edit_shop_orders')) {
            return $products;
        }
        return array();
    }

    /**
     * Enqueue assets on order edit screen
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets($hook)
    {
        // Only load on custom order edit page - check both hook and screen
        $is_order_edit_page = false;

        // Check hook first
        if (isset($_GET['page']) && $_GET['page'] === 'polar-express-order-edit') {
            $is_order_edit_page = true;
        }

        // Also check screen ID if available
        $screen = get_current_screen();
        if ($screen && isset($screen->id)) {
            $screen_id = (string) $screen->id;
            if (strpos($screen_id, 'polar-express-order-edit') !== false || strpos($screen_id, 'polar-express') !== false) {
                $is_order_edit_page = true;
            }
        }

        if (!$is_order_edit_page) {
            return;
        }

        // CSS should load for anyone on the order edit page - no permission check needed
        // Permission checks are handled in render_order_edit_page() and AJAX handlers
        // Blocking CSS based on permissions breaks the UI unnecessarily

        // Enqueue main dashboard styles for consistency
        wp_enqueue_style(
            'pexpress-admin',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar.css',
            array(),
            time() // Cache busting for dev
        );

        wp_enqueue_style(
            'pexpress-order-edit',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar-order-edit.css',
            array('pexpress-admin'),
            time() // Cache busting for dev
        );

        // Enqueue Select2 (WooCommerce includes it, but ensure it's loaded)
        if (!wp_script_is('select2', 'enqueued') && !wp_script_is('select2', 'registered')) {
            wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
            wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
        } else {
            wp_enqueue_script('select2');
            wp_enqueue_style('select2');
        }

        // Ensure WooCommerce backbone modal is available
        if (class_exists('WooCommerce')) {
            // WooCommerce admin scripts are typically loaded on order edit pages
            // But we need to ensure wc-backbone-modal is available
            if (!wp_script_is('wc-backbone-modal', 'registered')) {
                // Try to register it if WooCommerce provides it
                if (file_exists(WC()->plugin_path() . '/assets/js/admin/backbone-modal.min.js')) {
                    wp_register_script(
                        'wc-backbone-modal',
                        WC()->plugin_url() . '/assets/js/admin/backbone-modal.min.js',
                        array('jquery', 'wp-util', 'jquery-ui-dialog'),
                        WC_VERSION,
                        true
                    );
                }
            }
            if (wp_script_is('wc-backbone-modal', 'registered')) {
                wp_enqueue_script('wc-backbone-modal');
            }
        }

        // Enqueue order edit modules in correct order
        $modules = array(
            'utils',
            'api',
            'select2',
            'item-actions',
            'add-product',
            'forwarding',
            'shipping',
            'order-actions',
            'history',
        );

        $prev_handle = 'select2';
        foreach ($modules as $module) {
            $handle = 'pexpress-order-edit-' . $module;
            $file_path = PEXPRESS_PLUGIN_DIR . 'assets/js/order-edit/' . $module . '.js';
            $version = file_exists($file_path) ? filemtime($file_path) : PEXPRESS_VERSION;
            wp_enqueue_script(
                $handle,
                PEXPRESS_PLUGIN_URL . 'assets/js/order-edit/' . $module . '.js',
                array('jquery', $prev_handle),
                $version,
                true
            );
            $prev_handle = $handle;
        }

        // Main entry point (depends on all modules)
        $main_file_path = PEXPRESS_PLUGIN_DIR . 'assets/js/polar-order-edit.js';
        $main_version = file_exists($main_file_path) ? filemtime($main_file_path) : PEXPRESS_VERSION;
        wp_enqueue_script(
            'pexpress-order-edit',
            PEXPRESS_PLUGIN_URL . 'assets/js/polar-order-edit.js',
            array('jquery', 'select2', 'wp-util', $prev_handle),
            $main_version,
            true
        );

        // Get WooCommerce product search nonce
        // WooCommerce uses 'search-products' nonce for its product search AJAX
        $wc_search_nonce = wp_create_nonce('search-products');

        // Get order ID from URL
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;

        wp_localize_script(
            'pexpress-order-edit',
            'polarOrderEdit',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('polar_order_edit_nonce'),
                'orderId' => $order_id,
                'wcSearchNonce' => $wc_search_nonce,
                'currency' => array(
                    'symbol' => get_woocommerce_currency_symbol(),
                    'price_format' => get_woocommerce_price_format(),
                    'decimals' => wc_get_price_decimals(),
                    'decimal_separator' => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'locale' => get_locale(),
                ),
                'i18n' => array(
                    'searchProducts' => __('Search for a product...', 'pexpress'),
                    'selectProduct' => __('Please select a product.', 'pexpress'),
                    'confirmRemove' => __('Are you sure you want to remove this item?', 'pexpress'),
                    'invalidQuantity' => __('Please enter a valid quantity.', 'pexpress'),
                    'invalidPrice' => __('Please enter a valid price.', 'pexpress'),
                    'saveItem' => __('Save', 'pexpress'),
                    'cancelEdit' => __('Cancel', 'pexpress'),
                    'quantityLabel' => __('Quantity', 'pexpress'),
                    'priceLabel' => __('Price', 'pexpress'),
                    'addProducts' => __('Add products', 'pexpress'),
                    'addToOrder' => __('Add to order', 'pexpress'),
                    'cancel' => __('Cancel', 'pexpress'),
                    'closeModal' => __('Close modal', 'pexpress'),
                    'modalProductLabel' => __('Product', 'pexpress'),
                    'modalQuantityLabel' => __('Quantity', 'pexpress'),
                    'addProductError' => __('Error adding item.', 'pexpress'),
                    'genericError' => __('An error occurred. Please try again.', 'pexpress'),
                    'forwarding' => __('Forwarding to Distribution...', 'pexpress'),
                    'forwardSuccess' => __('Order forwarded to Distribution.', 'pexpress'),
                    'forwardError' => __('Unable to forward order. Please try again.', 'pexpress'),
                    'awaitingAssignment' => __('Awaiting Distribution Assignment', 'pexpress'),
                    'updateForwarding' => __('Update Forwarding', 'pexpress'),
                    'revokeConfirm' => __('Are you sure you want to revoke this order from SR?', 'pexpress'),
                    'revoking' => __('Revoking from SR...', 'pexpress'),
                    'revokeSuccess' => __('Order revoked from SR successfully.', 'pexpress'),
                    'revokeError' => __('Unable to revoke order. Please try again.', 'pexpress'),
                    'notForwarded' => __('Not Yet Forwarded', 'pexpress'),
                    'forwardToHR' => __('Forward to SR', 'pexpress'),
                    'shippingSaving' => __('Saving...', 'pexpress'),
                    'shippingSaved' => __('Address updated.', 'pexpress'),
                ),
            )
        );

        // Enqueue select2 if not already loaded
        wp_enqueue_script('select2');
        wp_enqueue_style('select2');
    }

    /**
     * Fallback method to ensure CSS loads even if admin_enqueue_scripts hook fails
     * This ensures CSS loads for all users regardless of capabilities
     */
    public function enqueue_assets_fallback()
    {
        // Only run if assets weren't already enqueued
        if (wp_style_is('pexpress-order-edit', 'enqueued')) {
            return;
        }

        // Check if we're on the order edit page
        $is_order_edit_page = false;

        // Check page parameter from URL
        if (isset($_GET['page']) && $_GET['page'] === 'polar-express-order-edit') {
            $is_order_edit_page = true;
        }

        // Also check screen ID if available
        if (!$is_order_edit_page) {
            $screen = get_current_screen();
            if ($screen && isset($screen->id)) {
                $screen_id = (string) $screen->id;
                if (strpos($screen_id, 'polar-express-order-edit') !== false || strpos($screen_id, 'polar-express') !== false) {
                    $is_order_edit_page = true;
                }
            }
        }

        if (!$is_order_edit_page) {
            return;
        }

        // Enqueue styles
        wp_enqueue_style(
            'pexpress-admin',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar.css',
            array(),
            PEXPRESS_VERSION
        );

        wp_enqueue_style(
            'pexpress-order-edit',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar-order-edit.css',
            array('pexpress-admin'),
            PEXPRESS_VERSION
        );

        // Enqueue Select2 if not already loaded
        if (!wp_script_is('select2', 'enqueued') && !wp_script_is('select2', 'registered')) {
            wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
            wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
        } else {
            wp_enqueue_script('select2');
            wp_enqueue_style('select2');
        }

        // Enqueue order edit modules in correct order
        $modules = array(
            'utils',
            'api',
            'select2',
            'item-actions',
            'add-product',
            'forwarding',
            'shipping',
            'order-actions',
            'history',
        );

        $prev_handle = 'select2';
        foreach ($modules as $module) {
            $handle = 'pexpress-order-edit-' . $module;
            $file_path = PEXPRESS_PLUGIN_DIR . 'assets/js/order-edit/' . $module . '.js';
            $version = file_exists($file_path) ? filemtime($file_path) : PEXPRESS_VERSION;
            wp_enqueue_script(
                $handle,
                PEXPRESS_PLUGIN_URL . 'assets/js/order-edit/' . $module . '.js',
                array('jquery', $prev_handle),
                $version,
                true
            );
            $prev_handle = $handle;
        }

        // Main entry point (depends on all modules)
        $main_file_path = PEXPRESS_PLUGIN_DIR . 'assets/js/polar-order-edit.js';
        $main_version = file_exists($main_file_path) ? filemtime($main_file_path) : PEXPRESS_VERSION;
        wp_enqueue_script(
            'pexpress-order-edit',
            PEXPRESS_PLUGIN_URL . 'assets/js/polar-order-edit.js',
            array('jquery', 'select2', 'wp-util', $prev_handle),
            $main_version,
            true
        );

        // Get order ID from URL
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        $wc_search_nonce = wp_create_nonce('search-products');

        wp_localize_script(
            'pexpress-order-edit',
            'polarOrderEdit',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('polar_order_edit_nonce'),
                'orderId' => $order_id,
                'wcSearchNonce' => $wc_search_nonce,
                'currency' => array(
                    'symbol' => get_woocommerce_currency_symbol(),
                    'price_format' => get_woocommerce_price_format(),
                    'decimals' => wc_get_price_decimals(),
                    'decimal_separator' => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'locale' => get_locale(),
                ),
                'i18n' => array(
                    'searchProducts' => __('Search for a product...', 'pexpress'),
                    'selectProduct' => __('Please select a product.', 'pexpress'),
                    'confirmRemove' => __('Are you sure you want to remove this item?', 'pexpress'),
                    'invalidQuantity' => __('Please enter a valid quantity.', 'pexpress'),
                    'invalidPrice' => __('Please enter a valid price.', 'pexpress'),
                    'saveItem' => __('Save', 'pexpress'),
                    'cancelEdit' => __('Cancel', 'pexpress'),
                    'quantityLabel' => __('Quantity', 'pexpress'),
                    'priceLabel' => __('Price', 'pexpress'),
                    'addProducts' => __('Add products', 'pexpress'),
                    'addToOrder' => __('Add to order', 'pexpress'),
                    'cancel' => __('Cancel', 'pexpress'),
                    'closeModal' => __('Close modal', 'pexpress'),
                    'modalProductLabel' => __('Product', 'pexpress'),
                    'modalQuantityLabel' => __('Quantity', 'pexpress'),
                    'addProductError' => __('Error adding item.', 'pexpress'),
                    'genericError' => __('An error occurred. Please try again.', 'pexpress'),
                ),
            )
        );
    }

    /**
     * Render custom order edit page
     */
    public function render_order_edit_page()
    {
        // Check permissions - allow any Polar Express role or users with edit_shop_orders capability
        $current_user = wp_get_current_user();
        $has_polar_role = false;

        // Check for Polar Express roles
        $polar_roles = array('polar_support', 'polar_hr', 'polar_delivery', 'polar_fridge', 'polar_distributor');
        foreach ($polar_roles as $role) {
            if (in_array($role, $current_user->roles, true)) {
                $has_polar_role = true;
                break;
            }
        }

        if (!$has_polar_role && !current_user_can('edit_shop_orders')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        if ($this->is_hr_only_user()) {
            wp_die(__('Distribution users cannot edit orders. Please use the Agency dashboard for assignments.', 'pexpress'));
        }

        // Get order ID from URL
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        if (!$order_id) {
            wp_die(__('Invalid order ID.', 'pexpress'));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die(__('Order not found.', 'pexpress'));
        }

        // Get order data with null safety
        $order_items = $order->get_items() ? $order->get_items() : array();
        $modification_log = $this->get_modification_log($order_id);
        $delivery_id = PExpress_Core::get_delivery_user_id($order_id);
        $fridge_id = PExpress_Core::get_fridge_user_id($order_id);
        $distributor_id = PExpress_Core::get_distributor_user_id($order_id);
        $needs_assignment = PExpress_Core::order_needs_assignment($order_id);
        $forwarded_at = PExpress_Core::get_order_meta($order_id, '_polar_forwarded_at');
        $forwarded_by = (int) PExpress_Core::get_order_meta($order_id, '_polar_forwarded_by');
        $forward_note = PExpress_Core::get_order_meta($order_id, '_polar_forward_note');

        // Extra info for Support Portal
        $order_number = $order->get_order_number();
        $payment_method_title = $order->get_payment_method_title();
        $customer_id = $order->get_customer_id();
        $customer_url = $customer_id ? get_edit_user_link($customer_id) : '';
        $order_subtotal = $order->get_subtotal();
        $customer_note = $order->get_customer_note();
        $order_confirmed_at = PExpress_Core::get_order_meta($order_id, '_polar_order_confirmed');
        $order_completed_at = PExpress_Core::get_order_meta($order_id, '_polar_order_completed');
        $meeting_type = PExpress_Core::get_meeting_type($order_id);
        $meeting_location = PExpress_Core::get_meeting_location($order_id);
        $meeting_datetime = PExpress_Core::get_meeting_datetime($order_id);
        $meeting_datetime_display = '';
        if (!empty($meeting_datetime)) {
            $ts = strtotime($meeting_datetime);
            $meeting_datetime_display = $ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts) : $meeting_datetime;
        }
        $shipping_formatted = $order->get_formatted_shipping_address();
        $shipping_address_1 = $order->get_shipping_address_1();
        $shipping_address_2 = $order->get_shipping_address_2();
        $shipping_city = $order->get_shipping_city();
        $shipping_state = $order->get_shipping_state();
        $shipping_postcode = $order->get_shipping_postcode();
        $shipping_country = $order->get_shipping_country();
        $shipping_first_name = $order->get_shipping_first_name();
        $shipping_last_name = $order->get_shipping_last_name();
        $shipping_company = $order->get_shipping_company();

        // Stage-wise tracking for panel
        $stage_wise = PExpress_Core::get_stage_wise_tracking($order_id);

        // Products for Add Product dropdown (simple list, no search)
        $products_dropdown = array();
        if (function_exists('wc_get_products')) {
            $products = wc_get_products(array(
                'status' => 'publish',
                'limit' => 300,
                'orderby' => 'title',
                'order' => 'ASC',
                'return' => 'ids',
                'exclude' => array(),
            ));
            foreach ($products as $pid) {
                $product = wc_get_product($pid);
                if ($product && !$product->is_type('variable')) {
                    $products_dropdown[$pid] = $product->get_name();
                }
            }
        }

        // Make variables available to template
        $order_id = $order_id;
        $order = $order;
        $order_items = $order_items;
        $modification_log = $modification_log;
        $delivery_id = $delivery_id ? $delivery_id : 0;
        $fridge_id = $fridge_id ? $fridge_id : 0;
        $distributor_id = $distributor_id ? $distributor_id : 0;
        $needs_assignment = $needs_assignment;
        $forwarded_at = $forwarded_at;
        $forwarded_by = $forwarded_by;
        $forward_note = $forward_note ? $forward_note : '';
        $order_number = isset($order_number) ? $order_number : '';
        $payment_method_title = isset($payment_method_title) && $payment_method_title ? $payment_method_title : '';
        $customer_id = isset($customer_id) ? (int) $customer_id : 0;
        $customer_url = isset($customer_url) ? $customer_url : '';
        $order_subtotal = isset($order_subtotal) ? $order_subtotal : 0;
        $customer_note = isset($customer_note) ? $customer_note : '';
        $order_confirmed_at = isset($order_confirmed_at) ? $order_confirmed_at : '';
        $order_completed_at = isset($order_completed_at) ? $order_completed_at : '';
        $meeting_type = isset($meeting_type) ? $meeting_type : '';
        $meeting_location = isset($meeting_location) ? $meeting_location : '';
        $meeting_datetime_display = isset($meeting_datetime_display) ? $meeting_datetime_display : '';
        $shipping_formatted = isset($shipping_formatted) ? $shipping_formatted : '';
        $shipping_address_1 = isset($shipping_address_1) ? $shipping_address_1 : '';
        $shipping_address_2 = isset($shipping_address_2) ? $shipping_address_2 : '';
        $shipping_city = isset($shipping_city) ? $shipping_city : '';
        $shipping_state = isset($shipping_state) ? $shipping_state : '';
        $shipping_postcode = isset($shipping_postcode) ? $shipping_postcode : '';
        $shipping_country = isset($shipping_country) ? $shipping_country : '';
        $shipping_first_name = isset($shipping_first_name) ? $shipping_first_name : '';
        $shipping_last_name = isset($shipping_last_name) ? $shipping_last_name : '';
        $shipping_company = isset($shipping_company) ? $shipping_company : '';
        $products_dropdown = isset($products_dropdown) && is_array($products_dropdown) ? $products_dropdown : array();
        $stage_wise = isset($stage_wise) && is_array($stage_wise) ? $stage_wise : array();

        // Include the template
        include PEXPRESS_PLUGIN_DIR . 'templates/order-edit.php';
    }

    /**
     * Add order manipulation UI to order edit screen
     *
     * @param WC_Order $order Order object.
     */
    public function add_order_manipulation_ui($order)
    {
        // Check permissions
        $current_user = wp_get_current_user();
        if (!in_array('polar_support', $current_user->roles) && !current_user_can('edit_shop_orders')) {
            return;
        }

        $order_id = $order->get_id();
        $modification_log = $this->get_modification_log($order_id);
        ?>
        <div class="polar-order-manipulation-wrapper">
            <h3><?php esc_html_e('Order Manipulation', 'pexpress'); ?></h3>

            <div class="polar-add-item-section">
                <h4><?php esc_html_e('Add Item to Order', 'pexpress'); ?></h4>
                <div class="polar-add-item-form">
                    <select id="polar-product-search" class="polar-product-select" style="width: 100%;">
                        <option value=""><?php esc_html_e('Search for a product...', 'pexpress'); ?></option>
                    </select>
                    <input type="number" id="polar-item-quantity" min="1" value="1"
                        placeholder="<?php esc_attr_e('Quantity', 'pexpress'); ?>" />
                    <button type="button" class="button button-primary polar-add-item-btn"
                        data-order-id="<?php echo esc_attr($order_id); ?>">
                        <?php esc_html_e('Add to Order', 'pexpress'); ?>
                    </button>
                </div>
            </div>

            <div class="polar-modification-history">
                <h4>
                    <?php esc_html_e('Modification History', 'pexpress'); ?>
                    <span class="polar-toggle-history dashicons dashicons-arrow-down-alt2"></span>
                </h4>
                <div class="polar-history-content" style="display: none;">
                    <?php if (empty($modification_log)): ?>
                        <p><?php esc_html_e('No modifications recorded.', 'pexpress'); ?></p>
                    <?php else: ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date/Time', 'pexpress'); ?></th>
                                    <th><?php esc_html_e('User', 'pexpress'); ?></th>
                                    <th><?php esc_html_e('Action', 'pexpress'); ?></th>
                                    <th><?php esc_html_e('Details', 'pexpress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($modification_log) as $log_entry): ?>
                                    <tr class="polar-log-entry polar-log-<?php echo esc_attr($log_entry['action']); ?>">
                                        <td><?php echo esc_html($log_entry['timestamp']); ?></td>
                                        <td><?php echo esc_html($log_entry['user_name']); ?></td>
                                        <td>
                                            <span class="polar-action-badge polar-action-<?php echo esc_attr($log_entry['action']); ?>">
                                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $log_entry['action']))); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            if (!empty($log_entry['old_value']) || !empty($log_entry['new_value'])) {
                                                echo '<div class="polar-log-details">';
                                                if (!empty($log_entry['old_value'])) {
                                                    echo '<strong>Before:</strong> ';
                                                    echo esc_html($this->format_log_value($log_entry['old_value']));
                                                    echo '<br>';
                                                }
                                                if (!empty($log_entry['new_value'])) {
                                                    echo '<strong>After:</strong> ';
                                                    echo esc_html($this->format_log_value($log_entry['new_value']));
                                                }
                                                echo '</div>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Format log value for display
     *
     * @param array $value Log value array.
     * @return string
     */
    private function format_log_value($value)
    {
        if (!is_array($value)) {
            return (string) $value;
        }

        $parts = array();
        if (isset($value['product_id'])) {
            $product = wc_get_product($value['product_id']);
            $parts[] = 'Product: ' . ($product ? $product->get_name() : 'ID ' . $value['product_id']);
        }
        if (isset($value['quantity'])) {
            $parts[] = 'Qty: ' . $value['quantity'];
        }
        if (isset($value['price'])) {
            $parts[] = 'Price: ' . wc_price($value['price']);
        }
        if (isset($value['address_1']) || isset($value['city']) || isset($value['postcode'])) {
            $addr = array_filter(array(
                isset($value['address_1']) ? $value['address_1'] : '',
                isset($value['address_2']) ? $value['address_2'] : '',
                isset($value['city']) ? $value['city'] : '',
                isset($value['state']) ? $value['state'] : '',
                isset($value['postcode']) ? $value['postcode'] : '',
                isset($value['country']) ? $value['country'] : '',
            ));
            $parts[] = 'Address: ' . implode(', ', $addr);
        }

        return implode(', ', $parts);
    }

    /**
     * AJAX handler: Add item to order
     */
    public function ajax_add_order_item()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $quantity_input = isset($_POST['quantity']) ? wp_unslash($_POST['quantity']) : 1;
        $quantity = wc_stock_amount($quantity_input);

        if (!$order_id || !$product_id || $quantity <= 0) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => __('Product not found.', 'pexpress')));
        }

        if (\Automattic\WooCommerce\Enums\ProductType::VARIABLE === $product->get_type()) {
            wp_send_json_error(array('message' => __('Variable product parents cannot be added directly.', 'pexpress')));
        }

        $validation_error = apply_filters('woocommerce_ajax_add_order_item_validation', new WP_Error(), $product, $order, $quantity);
        if ($validation_error instanceof WP_Error && $validation_error->get_error_code()) {
            wp_send_json_error(array('message' => $validation_error->get_error_message()));
        }

        $old_total = $order->get_total();

        $item_id = $order->add_product($product, $quantity, array('order' => $order));
        if (!$item_id) {
            wp_send_json_error(array('message' => __('Failed to add item.', 'pexpress')));
        }

        $item = $order->get_item($item_id);
        if ($item instanceof WC_Order_Item) {
            $item = apply_filters('woocommerce_ajax_order_item', $item, $item_id, $order, $product);
            
            // PEXPRESS: FIX DISCOUNT CALCULATION
            if ($item instanceof WC_Order_Item_Product) {
                $item_product = $item->get_product();
                if ($item_product) {
                    $quantity = $item->get_quantity();
                    $regular_price = $item_product->get_regular_price();
                    if ($regular_price === '') {
                        $regular_price = $item_product->get_price();
                    }
                    $regular_price = (float) $regular_price;

                    $discounted_price = $regular_price;
                    if (class_exists('\Wdr\App\Controllers\ManageDiscount')) {
                        $discount_res = \Wdr\App\Controllers\ManageDiscount::calculateProductDiscountPrice($regular_price, $item_product, $quantity);
                        if (!empty($discount_res) && is_numeric($discount_res)) {
                            $discounted_price = (float) $discount_res;
                        }
                    } elseif ($item_product->get_price() < $regular_price) {
                        $discounted_price = (float) $item_product->get_price();
                    }

                    $item->set_subtotal((float) $regular_price * $quantity);
                    $item->set_total((float) $discounted_price * $quantity);
                    $item->save();
                }
                
                // Fix bundled children if any were added alongside
                if (function_exists('wc_pb_get_bundled_order_items')) {
                    $bundled_items = wc_pb_get_bundled_order_items($item, $order);
                    if (!empty($bundled_items)) {
                        foreach ($bundled_items as $child_item) {
                            if ($child_item instanceof WC_Order_Item_Product) {
                                $child_product = $child_item->get_product();
                                if ($child_product) {
                                    $child_qty = $child_item->get_quantity();
                                    $child_reg_price = $child_product->get_regular_price();
                                    if ($child_reg_price === '') {
                                        $child_reg_price = $child_product->get_price();
                                    }
                                    $child_reg_price = (float) $child_reg_price;

                                    $child_discounted = $child_reg_price;
                                    if (class_exists('\Wdr\App\Controllers\ManageDiscount')) {
                                        $discount_res = \Wdr\App\Controllers\ManageDiscount::calculateProductDiscountPrice($child_reg_price, $child_product, $child_qty);
                                        if (!empty($discount_res) && is_numeric($discount_res)) {
                                            $child_discounted = (float) $discount_res;
                                        }
                                    } elseif ($child_product->get_price() < $child_reg_price) {
                                        $child_discounted = (float) $child_product->get_price();
                                    }

                                    $child_item->set_subtotal((float) $child_reg_price * $child_qty);
                                    $child_item->set_total((float) $child_discounted * $child_qty);
                                    $child_item->save();
                                }
                            }
                        }
                    }
                }
            }
            
            do_action('woocommerce_ajax_add_order_item_meta', $item_id, $item, $order);
        }

        $order->calculate_totals(true);
        $order->save();

        $added_items = array();
        if ($item instanceof WC_Order_Item) {
            $added_items[$item_id] = $item;
        }

        if (!empty($added_items)) {
            do_action('woocommerce_ajax_order_items_added', $added_items, $order);
            $order->add_order_note(sprintf(__('Added line items: %s', 'woocommerce'), $product->get_formatted_name()), false, true);
        }

        $new_total = $order->get_total();

        $new_value = null;
        if ($item instanceof WC_Order_Item_Product) {
            $new_value = array(
                'product_id' => $item->get_product_id(),
                'quantity' => $item->get_quantity(),
                'price' => wc_format_decimal($item->get_total()),
            );
        }

        $this->log_order_modification(
            $order_id,
            'item_added',
            null,
            $new_value,
            $old_total,
            $new_total
        );

        wp_send_json_success(array(
            'message' => __('Item added successfully.', 'pexpress'),
            'order_total' => $new_total,
            'item_id' => $item_id,
        ));
    }

    /**
     * AJAX handler: Remove item from order
     */
    public function ajax_remove_order_item()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;

        if (!$order_id || !$item_id) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $item = $order->get_item($item_id);
        if (!$item || !($item instanceof WC_Order_Item_Product)) {
            wp_send_json_error(array('message' => __('Item not found.', 'pexpress')));
        }
        /** @var WC_Order_Item_Product $item */

        $old_total = $order->get_total();
        $old_value = array(
            'product_id' => $item->get_product_id(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
        );

        // Remove item
        $order->remove_item($item_id);
        $order->calculate_totals();
        $order->save();

        $new_total = $order->get_total();

        // Log modification
        $this->log_order_modification(
            $order_id,
            'item_removed',
            $old_value,
            null,
            $old_total,
            $new_total
        );

        wp_send_json_success(array(
            'message' => __('Item removed successfully.', 'pexpress'),
            'order_total' => $new_total,
        ));
    }

    /**
     * AJAX handler: Update order item
     */
    public function ajax_update_order_item()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;

        $quantity_provided = array_key_exists('quantity', $_POST);
        $price_provided = array_key_exists('price', $_POST);

        $quantity = $quantity_provided ? absint($_POST['quantity']) : null;

        $price = null;
        if ($price_provided) {
            $price_raw = wc_clean(wp_unslash($_POST['price']));
            if ($price_raw === '') {
                $price_provided = false;
            } elseif (!is_numeric($price_raw)) {
                wp_send_json_error(array('message' => __('Invalid price value.', 'pexpress')));
            } else {
                $price = (float) wc_format_decimal($price_raw);
            }
        }

        if (!$order_id || !$item_id) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'pexpress')));
        }

        if (!$quantity_provided && !$price_provided) {
            wp_send_json_error(array('message' => __('No changes specified.', 'pexpress')));
        }

        if ($quantity_provided && $quantity < 1) {
            wp_send_json_error(array('message' => __('Quantity must be at least 1.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $item = $order->get_item($item_id);
        /** @var WC_Order_Item_Product|null $item */
        if (!$item) {
            wp_send_json_error(array('message' => __('Item not found.', 'pexpress')));
        }

        $old_total = $order->get_total();
        $old_value = array(
            'product_id' => $item->get_product_id(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
        );

        $existing_quantity = max($item->get_quantity(), 1);
        $existing_line_total = (float) $item->get_total();
        $existing_line_subtotal = (float) $item->get_subtotal();
        $unit_price_discounted = $existing_quantity > 0 ? $existing_line_total / $existing_quantity : 0;
        $unit_price_actual = $existing_quantity > 0 ? $existing_line_subtotal / $existing_quantity : 0;

        $quantity_changed = false;
        $new_quantity = $item->get_quantity();
        if ($quantity_provided && $quantity > 0 && $quantity !== $item->get_quantity()) {
            $item->set_quantity($quantity);
            $new_quantity = $quantity;
            $quantity_changed = true;
        }

        if ($price_provided && $price !== null) {
            $unit_price_discounted = (float) wc_format_decimal($price);
        }

        if ($price_provided || $quantity_changed) {
            $new_quantity_for_calc = max($new_quantity, 1);
            if ($price_provided && $price !== null) {
                $line_total = (float) wc_format_decimal($unit_price_discounted * $new_quantity_for_calc);
                $item->set_subtotal($line_total);
                $item->set_total($line_total);
            } else {
                $item_product = $item->get_product();
                if ($item_product) {
                    $regular_price = $item_product->get_regular_price();
                    if ($regular_price === '') {
                        $regular_price = $item_product->get_price();
                    }
                    $regular_price = (float) $regular_price;

                    $discounted_price = $regular_price;
                    if (class_exists('\Wdr\App\Controllers\ManageDiscount')) {
                        $discount_res = \Wdr\App\Controllers\ManageDiscount::calculateProductDiscountPrice($regular_price, $item_product, $new_quantity_for_calc);
                        if (!empty($discount_res) && is_numeric($discount_res)) {
                            $discounted_price = (float) $discount_res;
                        }
                    } elseif ($item_product->get_price() < $regular_price) {
                        $discounted_price = (float) $item_product->get_price();
                    }

                    $new_subtotal = (float) wc_format_decimal($regular_price * $new_quantity_for_calc);
                    $new_total = (float) wc_format_decimal($discounted_price * $new_quantity_for_calc);
                } else {
                    $new_subtotal = (float) wc_format_decimal($unit_price_actual * $new_quantity_for_calc);
                    $new_total = (float) wc_format_decimal($unit_price_discounted * $new_quantity_for_calc);
                }
                
                $item->set_subtotal($new_subtotal);
                $item->set_total($new_total);
            }
            $item->set_subtotal_tax(0);
            $item->set_total_tax(0);
            $item->set_taxes(array());
        }

        $item->save();

        // PEXPRESS: Handle Bundle Update
        // If this item is a bundle container, we need to update its children
        if ($quantity_changed && function_exists('wc_pb_is_bundle_container_order_item') && wc_pb_is_bundle_container_order_item($item)) {
            $old_quantity = max($existing_quantity, 1);
            $ratio = $new_quantity / $old_quantity;

            if (function_exists('wc_pb_get_bundled_order_items')) {
                $bundled_items = wc_pb_get_bundled_order_items($item, $order);

                if (!empty($bundled_items)) {
                    foreach ($bundled_items as $child_item) {
                        /** @var WC_Order_Item_Product $child_item */
                        $child_old_qty = $child_item->get_quantity();
                        $child_new_qty = max(round($child_old_qty * $ratio), 1); // Ensure at least 1

                        if ($child_new_qty != $child_old_qty) {
                            $child_item->set_quantity($child_new_qty);

                            // Update totals for child
                            $child_product = $child_item->get_product();
                            if ($child_product) {
                                $child_reg_price = $child_product->get_regular_price();
                                if ($child_reg_price === '') {
                                    $child_reg_price = $child_product->get_price();
                                }
                                $child_reg_price = (float) $child_reg_price;

                                $child_discounted = $child_reg_price;
                                if (class_exists('\Wdr\App\Controllers\ManageDiscount')) {
                                    $discount_res = \Wdr\App\Controllers\ManageDiscount::calculateProductDiscountPrice($child_reg_price, $child_product, $child_new_qty);
                                    if (!empty($discount_res) && is_numeric($discount_res)) {
                                        $child_discounted = (float) $discount_res;
                                    }
                                } elseif ($child_product->get_price() < $child_reg_price) {
                                    $child_discounted = (float) $child_product->get_price();
                                }

                                $child_item->set_subtotal((float) wc_format_decimal($child_reg_price * $child_new_qty));
                                $child_item->set_total((float) wc_format_decimal($child_discounted * $child_new_qty));
                            } else {
                                $child_total = $child_item->get_total();
                                $child_unit_price = ($child_old_qty > 0) ? $child_total / $child_old_qty : 0;
                                $new_child_total = wc_format_decimal($child_unit_price * $child_new_qty);
                                $child_item->set_subtotal($new_child_total);
                                $child_item->set_total($new_child_total);
                            }
                            $child_item->save();
                        }
                    }
                    $order->add_order_note(sprintf(__('Bundle quantity updated. Synced %d child items.', 'pexpress'), count($bundled_items)));
                }
            }
        }

        $order->calculate_totals(true);
        $order->save();

        $new_total = $order->get_total();
        $new_value = array(
            'product_id' => $item->get_product_id(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
        );

        // Determine action type
        $action = 'item_updated';
        if ($quantity_provided && $price_provided) {
            $action = 'item_updated';
        } elseif ($quantity_provided) {
            $action = 'quantity_changed';
        } elseif ($price_provided) {
            $action = 'price_changed';
        }

        // Log modification
        $this->log_order_modification(
            $order_id,
            $action,
            $old_value,
            $new_value,
            $old_total,
            $new_total
        );

        wp_send_json_success(array(
            'message' => __('Item updated successfully.', 'pexpress'),
            'order_total' => $new_total,
        ));
    }

    /**
     * AJAX handler: Replace order item
     */
    public function ajax_replace_order_item()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        $new_product_id = isset($_POST['new_product_id']) ? absint($_POST['new_product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

        if (!$order_id || !$item_id || !$new_product_id) {
            wp_send_json_error(array('message' => __('Invalid parameters.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $item = $order->get_item($item_id);
        /** @var WC_Order_Item_Product|null $item */
        if (!$item) {
            wp_send_json_error(array('message' => __('Item not found.', 'pexpress')));
        }

        $new_product = wc_get_product($new_product_id);
        if (!$new_product) {
            wp_send_json_error(array('message' => __('New product not found.', 'pexpress')));
        }

        $old_total = $order->get_total();
        $old_value = array(
            'product_id' => $item->get_product_id(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
        );

        // Remove old item
        $order->remove_item($item_id);

        // Add new item
        $new_item_id = $order->add_product($new_product, $quantity);
        if (!$new_item_id) {
            wp_send_json_error(array('message' => __('Failed to add replacement item.', 'pexpress')));
        }
        
        // PEXPRESS: FIX DISCOUNT CALCULATION
        $new_item = $order->get_item($new_item_id);
        if ($new_item instanceof WC_Order_Item_Product) {
            $new_item_product = $new_item->get_product();
            if ($new_item_product) {
                $regular_price = $new_item_product->get_regular_price();
                if ($regular_price !== '' && $regular_price > $new_item_product->get_price()) {
                    $new_item->set_subtotal((float) $regular_price * $new_item->get_quantity());
                    $new_item->save();
                }
            }
            
            // Fix bundled children
            if (function_exists('wc_pb_get_bundled_order_items')) {
                $bundled_items = wc_pb_get_bundled_order_items($new_item, $order);
                if (!empty($bundled_items)) {
                    foreach ($bundled_items as $child_item) {
                        if ($child_item instanceof WC_Order_Item_Product) {
                            $child_product = $child_item->get_product();
                            if ($child_product) {
                                $child_reg_price = $child_product->get_regular_price();
                                if ($child_reg_price !== '' && $child_reg_price > $child_product->get_price()) {
                                    $child_item->set_subtotal((float) $child_reg_price * $child_item->get_quantity());
                                    $child_item->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        $order->calculate_totals();
        $order->save();

        $new_total = $order->get_total();
        $new_value = array(
            'product_id' => $new_product_id,
            'quantity' => $quantity,
            'price' => $new_product->get_price() * $quantity,
        );

        // Log modification
        $this->log_order_modification(
            $order_id,
            'item_replaced',
            $old_value,
            $new_value,
            $old_total,
            $new_total
        );

        wp_send_json_success(array(
            'message' => __('Item replaced successfully.', 'pexpress'),
            'order_total' => $new_total,
            'item_id' => $new_item_id,
        ));
    }

    /**
     * AJAX handler: Get product alternatives for replacement
     */
    public function ajax_get_product_alternatives()
    {
        $this->verify_request();

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        if (!$product_id) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'pexpress')));
        }

        $current_product = wc_get_product($product_id);
        if (!$current_product) {
            wp_send_json_error(array('message' => __('Product not found.', 'pexpress')));
        }

        // Get products in same category
        $categories = $current_product->get_category_ids();
        $args = array(
            'status' => 'publish',
            'limit' => 20,
            'exclude' => array($product_id),
        );

        if (!empty($categories)) {
            $args['category'] = $categories;
        }

        if (!empty($search_term)) {
            $args['s'] = $search_term;
        }

        $products = wc_get_products($args);
        $results = array();

        foreach ($products as $product) {
            $results[] = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'stock_status' => $product->get_stock_status(),
                'sku' => $product->get_sku(),
            );
        }

        wp_send_json_success(array('products' => $results));
    }

    /**
     * AJAX handler: Recalculate order totals
     */
    public function ajax_recalculate_order_totals()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;

        if (!$order_id) {
            wp_send_json_error(array('message' => __('Invalid order ID.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $old_total = $order->get_total();
        $order->calculate_totals();
        $order->save();
        $new_total = $order->get_total();

        wp_send_json_success(array(
            'message' => __('Totals recalculated.', 'pexpress'),
            'order_total' => $new_total,
            'old_total' => $old_total,
        ));
    }

    /**
     * AJAX handler: Forward order to HR
     */
    public function ajax_forward_order_to_hr()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if (!$order_id) {
            wp_send_json_error(array('message' => __('Invalid order ID.', 'pexpress')));
        }

        if ($this->is_hr_only_user()) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $note = isset($_POST['note']) ? sanitize_textarea_field(wp_unslash($_POST['note'])) : '';

        $user = wp_get_current_user();
        $timestamp = current_time('mysql');
        $display_timestamp = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
        $forward_summary = '';
        if ($display_timestamp && $user->display_name) {
            $forward_summary = sprintf(
                /* translators: 1: forward date, 2: user name */
                __('Last forwarded on %1$s by %2$s.', 'pexpress'),
                $display_timestamp,
                $user->display_name
            );
        } elseif ($display_timestamp) {
            $forward_summary = sprintf(
                /* translators: %s: forward date */
                __('Last forwarded on %s.', 'pexpress'),
                $display_timestamp
            );
        } elseif ($user->display_name) {
            $forward_summary = sprintf(
                /* translators: %s: user name */
                __('Forwarded by %s.', 'pexpress'),
                $user->display_name
            );
        }

        // Update assignment flags
        PExpress_Core::update_order_meta($order_id, '_polar_needs_assignment', 'yes');
        PExpress_Core::update_order_meta($order_id, '_polar_forwarded_by', $user->ID);
        PExpress_Core::update_order_meta($order_id, '_polar_forwarded_at', $timestamp);

        if ($note !== '') {
            PExpress_Core::update_order_meta($order_id, '_polar_forward_note', $note);
        } else {
            PExpress_Core::update_order_meta($order_id, '_polar_forward_note', '');
        }

        $current_status = $order->get_status();
        if ('processing' !== $current_status) {
            $order->update_status('processing', __('Order forwarded to Distribution for assignment.', 'pexpress'), false);
        }

        $order->add_order_note(
            sprintf(
                /* translators: %s: user name */
                __('Forwarded to Distribution by %s for assignment.', 'pexpress'),
                $user->display_name
            ),
            false,
            true
        );

        $this->log_order_modification(
            $order_id,
            'forwarded_to_hr',
            null,
            array(
                'forwarded_by' => $user->display_name,
                'note' => $note,
            ),
            $order->get_total(),
            $order->get_total()
        );

        wp_send_json_success(array(
            'message' => __('Order forwarded to Distribution.', 'pexpress'),
            'forwarded_at' => $display_timestamp,
            'forwarded_by' => $user->display_name,
            'note' => $note,
            'summary' => $forward_summary,
        ));
    }

    /**
     * AJAX handler: Revoke order from HR
     */
    public function ajax_revoke_order_from_hr()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if (!$order_id) {
            wp_send_json_error(array('message' => __('Invalid order ID.', 'pexpress')));
        }

        if ($this->is_hr_only_user()) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $user = wp_get_current_user();

        // Remove forward flags
        PExpress_Core::update_order_meta($order_id, '_polar_needs_assignment', 'no');
        PExpress_Core::update_order_meta($order_id, '_polar_forwarded_by', '');
        PExpress_Core::update_order_meta($order_id, '_polar_forwarded_at', '');
        PExpress_Core::update_order_meta($order_id, '_polar_forward_note', '');

        $order->add_order_note(
            sprintf(
                /* translators: %s: user name */
                __('Revoked from Distribution by %s.', 'pexpress'),
                $user->display_name
            ),
            false,
            true
        );

        $this->log_order_modification(
            $order_id,
            'revoked_from_hr',
            null,
            array(
                'revoked_by' => $user->display_name,
            ),
            $order->get_total(),
            $order->get_status()
        );

        wp_send_json_success(array(
            'message' => __('Order revoked from Distribution successfully.', 'pexpress'),
        ));
    }

    /**
     * AJAX handler: Update order shipping address
     */
    public function ajax_update_shipping_address()
    {
        $this->verify_request();

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if (!$order_id) {
            wp_send_json_error(array('message' => __('Invalid order ID.', 'pexpress')));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'pexpress')));
        }

        $old_address = array(
            'first_name' => $order->get_shipping_first_name(),
            'last_name' => $order->get_shipping_last_name(),
            'company' => $order->get_shipping_company(),
            'address_1' => $order->get_shipping_address_1(),
            'address_2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'state' => $order->get_shipping_state(),
            'postcode' => $order->get_shipping_postcode(),
            'country' => $order->get_shipping_country(),
        );

        $fields = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');
        $new_address = array();
        foreach ($fields as $field) {
            $key = 'shipping_' . $field;
            if (array_key_exists($key, $_POST)) {
                $new_address[$field] = sanitize_text_field(wp_unslash($_POST[$key]));
            } else {
                $new_address[$field] = isset($old_address[$field]) ? $old_address[$field] : '';
            }
        }

        $order->set_shipping_first_name($new_address['first_name']);
        $order->set_shipping_last_name($new_address['last_name']);
        $order->set_shipping_company($new_address['company']);
        $order->set_shipping_address_1($new_address['address_1']);
        $order->set_shipping_address_2($new_address['address_2']);
        $order->set_shipping_city($new_address['city']);
        $order->set_shipping_state($new_address['state']);
        $order->set_shipping_postcode($new_address['postcode']);
        $order->set_shipping_country($new_address['country']);
        $order->save();

        $user = wp_get_current_user();
        $order->add_order_note(
            sprintf(
                /* translators: %s: user name */
                __('Shipping address updated by %s.', 'pexpress'),
                $user->display_name
            ),
            false,
            true
        );

        $this->log_order_modification(
            $order_id,
            'shipping_address_updated',
            $old_address,
            $new_address,
            $order->get_total(),
            $order->get_total()
        );

        $formatted = $order->get_formatted_shipping_address();
        wp_send_json_success(array(
            'message' => __('Shipping address updated.', 'pexpress'),
            'formatted' => $formatted ? $formatted : '',
        ));
    }

    /**
     * Verify AJAX request
     */
    private function verify_request()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'polar_order_edit_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'pexpress')));
        }

        // Check permissions
        $current_user = wp_get_current_user();
        if (!in_array('polar_support', $current_user->roles) && !in_array('polar_hr', $current_user->roles) && !current_user_can('edit_shop_orders')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'pexpress')));
        }

        if ($this->is_hr_only_user()) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'pexpress')));
        }
    }

    /**
     * Log order modification
     *
     * @param int    $order_id        Order ID.
     * @param string $action          Action type.
     * @param array  $old_value       Old value.
     * @param array  $new_value       New value.
     * @param float  $order_total_before Old total.
     * @param float  $order_total_after  New total.
     */
    private function log_order_modification($order_id, $action, $old_value = null, $new_value = null, $order_total_before = null, $order_total_after = null)
    {
        $user = wp_get_current_user();
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'user_id' => $user->ID,
            'user_name' => $user->display_name,
            'action' => $action,
            'old_value' => $old_value,
            'new_value' => $new_value,
            'order_total_before' => $order_total_before,
            'order_total_after' => $order_total_after,
        );

        $log = $this->get_modification_log($order_id);
        $log[] = $log_entry;

        PExpress_Core::update_order_meta($order_id, '_polar_modification_log', $log);
    }

    /**
     * Get modification log for an order
     *
     * @param int $order_id Order ID.
     * @return array
     */
    private function get_modification_log($order_id)
    {
        $log = PExpress_Core::get_order_meta($order_id, '_polar_modification_log');
        return is_array($log) ? $log : array();
    }

    /**
     * Determine if current user is HR-only
     *
     * @return bool
     */
    private function is_hr_only_user()
    {
        $user = wp_get_current_user();
        if (!$user || empty($user->ID)) {
            return false;
        }

        $roles = (array) $user->roles;
        if (!in_array('polar_hr', $roles, true)) {
            return false;
        }

        $allowed_roles = array('administrator', 'shop_manager', 'polar_support');
        foreach ($allowed_roles as $role) {
            if (in_array($role, $roles, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add body class to order edit page for styling
     *
     * @param string $classes Body classes.
     * @return string Modified body classes.
     */
    public function add_order_edit_body_class($classes)
    {
        if (isset($_GET['page']) && $_GET['page'] === 'polar-express-order-edit') {
            $classes .= ' polar-order-edit-page';
        }
        return $classes;
    }
}
