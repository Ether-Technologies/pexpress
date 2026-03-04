<?php

/**
 * Admin dashboard rendering
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin dashboards handler
 */
class PExpress_Admin_Dashboards
{

    /**
     * Render Agency Dashboard page (formerly HR)
     */
    public function render_agency_dashboard()
    {
        $current_user = wp_get_current_user();
        if (!in_array('polar_hr', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        // Get orders needing assignment
        $pending_orders = wc_get_orders(array(
            'status' => 'processing',
            'limit' => -1,
            'meta_query' => array(
                array(
                    'key' => '_polar_needs_assignment',
                    'value' => 'yes',
                    'compare' => '=',
                ),
            ),
        ));

        // Get orders already assigned but not yet completed (for tabbed view).
        // Use exact status slugs from WooCommerce order status dropdown; exclude terminal (complete/cancelled/refunded/failed/draft).
        $in_progress_statuses = array(
            'wc-pending',
            'wc-processing',
            'wc-on-hold',
            'wc-polar-assigned',
            'wc-polar-distributor-prep',
            'wc-polar-out',
            'wc-polar-distributor-complete',
            'wc-polar-meet-point',
            'wc-polar-delivery-location',
            'wc-polar-service-progress',
            'wc-polar-service-complete',
            'wc-polar-fridge-drop',
            'wc-polar-fridge-back',
        );
        $assigned_in_progress_orders = wc_get_orders(array(
            'status' => $in_progress_statuses,
            'limit' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        $terminal_statuses = array('wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed', 'wc-checkout-draft', 'wc-polar-complete', 'wc-polar-delivered', 'wc-polar-fridge-returned');
        $assigned_in_progress_orders = array_filter($assigned_in_progress_orders, function ($order) use ($terminal_statuses) {
            if (!$order || !is_a($order, 'WC_Order')) {
                return false;
            }
            $status = $order->get_status();
            $normalized = (strpos($status, 'wc-') === 0) ? $status : 'wc-' . $status;
            if (in_array($status, $terminal_statuses, true) || in_array($normalized, $terminal_statuses, true)) {
                return false;
            }
            // Include order if it is not still "pending assignment" (show all non-pending in In Progress, with or without assignees)
            $needs = PExpress_Core::get_order_meta($order->get_id(), '_polar_needs_assignment');
            return $needs !== 'yes';
        });
        $assigned_in_progress_orders = array_values($assigned_in_progress_orders);

        // Get completed orders (exclude cancelled)
        $completed_orders = wc_get_orders(array(
            'status' => array('completed', 'wc-polar-complete', 'wc-polar-delivered', 'wc-polar-fridge-returned', 'wc-polar-service-complete', 'wc-polar-customer-served'),
            'limit' => 30,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        // Get all HR (formerly delivery), fridge, and distributor users
        $hr_users = get_users(array('role' => 'polar_delivery'));
        $fridge_users = get_users(array('role' => 'polar_fridge'));
        $distributor_users = get_users(array('role' => 'polar_distributor'));

        include PEXPRESS_PLUGIN_DIR . 'templates/hr-dashboard.php';
    }

    /**
     * Render Distribution Dashboard page (formerly Delivery)
     */
    public function render_hr_dashboard()
    {
        $current_user = wp_get_current_user();
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        if ($debug_enabled) {
            error_log(sprintf(
                '[PEXPRESS DEBUG] render_hr_dashboard - User ID: %d, Roles: %s, Has manage_woocommerce: %s',
                get_current_user_id(),
                implode(', ', $current_user->roles),
                current_user_can('manage_woocommerce') ? 'YES' : 'NO'
            ));
        }

        if (!in_array('polar_delivery', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $user_id = get_current_user_id();

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_hr_dashboard - Fetching orders for user ID: %d, role: delivery', $user_id));
        }

        // Get orders assigned to this HR person
        $assigned_orders = PExpress_Core::get_assigned_orders($user_id, 'delivery');

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_hr_dashboard - Received %d assigned orders', is_array($assigned_orders) ? count($assigned_orders) : 0));
        }

        // Output debug console logs
        PExpress_Core::output_debug_console();

        // Get orders by status
        $out_orders = array();
        $delivered_orders = array();

        foreach ($assigned_orders as $order) {
            if ($order->get_status() === 'wc-polar-out') {
                $out_orders[] = $order;
            } elseif ($order->get_status() === 'wc-polar-delivered') {
                $delivered_orders[] = $order;
            }
        }

        include PEXPRESS_PLUGIN_DIR . 'templates/delivery-dashboard.php';
    }

    /**
     * Render Fridge Dashboard page
     */
    public function render_fridge_dashboard()
    {
        $current_user = wp_get_current_user();
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        if ($debug_enabled) {
            error_log(sprintf(
                '[PEXPRESS DEBUG] render_fridge_dashboard - User ID: %d, Roles: %s, Has manage_woocommerce: %s',
                get_current_user_id(),
                implode(', ', $current_user->roles),
                current_user_can('manage_woocommerce') ? 'YES' : 'NO'
            ));
        }

        if (!in_array('polar_fridge', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $user_id = get_current_user_id();

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_fridge_dashboard - Fetching orders for user ID: %d, role: fridge', $user_id));
        }

        // Get orders assigned to this fridge provider
        $assigned_orders = PExpress_Core::get_assigned_orders($user_id, 'fridge');

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_fridge_dashboard - Received %d assigned orders', is_array($assigned_orders) ? count($assigned_orders) : 0));
        }

        // Output debug console logs
        PExpress_Core::output_debug_console();

        // Get orders by status
        $collected_orders = array();
        $return_pending = array();

        foreach ($assigned_orders as $order) {
            $return_date = PExpress_Core::get_order_meta($order->get_id(), '_polar_fridge_return_date');
            if ($order->get_status() === 'wc-polar-fridge-back') {
                $collected_orders[] = $order;
            } else {
                $return_pending[] = $order;
            }
        }

        include PEXPRESS_PLUGIN_DIR . 'templates/fridge-dashboard.php';
    }

    /**
     * Render Distributor Dashboard page
     */
    public function render_distributor_dashboard()
    {
        $current_user = wp_get_current_user();
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        if ($debug_enabled) {
            error_log(sprintf(
                '[PEXPRESS DEBUG] render_distributor_dashboard - User ID: %d, Roles: %s, Has manage_woocommerce: %s',
                get_current_user_id(),
                implode(', ', $current_user->roles),
                current_user_can('manage_woocommerce') ? 'YES' : 'NO'
            ));
        }

        if (!in_array('polar_distributor', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $user_id = get_current_user_id();

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_distributor_dashboard - Fetching orders for user ID: %d, role: distributor', $user_id));
        }

        // Get orders assigned to this distributor
        $assigned_orders = PExpress_Core::get_assigned_orders($user_id, 'distributor');

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_distributor_dashboard - Received %d assigned orders', is_array($assigned_orders) ? count($assigned_orders) : 0));
        }

        // Output debug console logs
        PExpress_Core::output_debug_console();

        include PEXPRESS_PLUGIN_DIR . 'templates/distributor-dashboard.php';
    }

    /**
     * Render Support Dashboard page
     */
    public function render_support_dashboard()
    {
        $current_user = wp_get_current_user();
        if (!in_array('polar_support', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        // Get recent orders
        $recent_orders = wc_get_orders(array(
            'status' => 'any',
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        // Ensure $recent_orders is always an array
        if (!is_array($recent_orders)) {
            $recent_orders = array();
        }

        include PEXPRESS_PLUGIN_DIR . 'templates/support-dashboard.php';
    }
}
