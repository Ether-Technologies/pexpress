<?php

/**
 * Custom Order Edit Page Template
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get order data safely
$order_id = isset($order_id) ? $order_id : 0;
$order = isset($order) ? $order : null;
$order_items = isset($order_items) ? $order_items : array();
$modification_log = isset($modification_log) ? $modification_log : array();
$delivery_id = isset($delivery_id) ? $delivery_id : 0;
$fridge_id = isset($fridge_id) ? $fridge_id : 0;
$distributor_id = isset($distributor_id) ? $distributor_id : 0;

if (!$order || !is_a($order, 'WC_Order')) {
?>
    <div class="wrap">
        <div class="notice notice-error">
            <p><?php esc_html_e('Order not found or invalid order ID.', 'pexpress'); ?></p>
        </div>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-support')); ?>" class="button">
                <?php esc_html_e('Back to Support Dashboard', 'pexpress'); ?>
            </a>
        </p>
    </div>
<?php
    return;
}

// Get safe values with null checks
$billing_email = $order->get_billing_email() ? $order->get_billing_email() : '';
$billing_phone = $order->get_billing_phone() ? $order->get_billing_phone() : '';
$billing_address = $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __('No address provided', 'pexpress');
$order_status = $order->get_status() ? $order->get_status() : 'pending';
$order_date = $order->get_date_created() ? $order->get_date_created()->date_i18n('F j, Y g:i A') : '';
$order_total = $order->get_formatted_order_total() ? $order->get_formatted_order_total() : wc_price(0);
$customer_name = PExpress_Core::get_billing_name($order);
$needs_assignment = isset($needs_assignment) ? (bool) $needs_assignment : false;
$forwarded_at = isset($forwarded_at) ? $forwarded_at : '';
$forwarded_by = isset($forwarded_by) ? (int) $forwarded_by : 0;
$forward_note = isset($forward_note) ? $forward_note : '';
$forwarded_by_user = $forwarded_by ? get_userdata($forwarded_by) : false;
$forwarded_by_name = $forwarded_by_user ? $forwarded_by_user->display_name : '';
$forwarded_at_display = '';
if (!empty($forwarded_at)) {
    $forwarded_at_display = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $forwarded_at);
}
$is_forwarded = !empty($forwarded_at) || !empty($forwarded_by);
$forward_button_label = $is_forwarded ? __('Update Forwarding', 'pexpress') : __('Forward to SR', 'pexpress');
?>

<div class="wrap polar-dashboard polar-order-edit-dashboard">
    <div class="polar-dashboard-header">
        <div class="polar-header-content">
            <h1 class="polar-dashboard-title">
                <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-support')); ?>"
                    class="polar-back-link" aria-label="<?php esc_attr_e('Back to Support Dashboard', 'pexpress'); ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </a>
                <span class="polar-title-icon">✏️</span>
                <?php esc_html_e('Edit Order', 'pexpress'); ?>
                <span class="order-id-badge">
                    #<?php echo esc_html($order_id); ?>
                </span>
            </h1>
            <p class="polar-dashboard-subtitle"><?php esc_html_e('Manage order items and details', 'pexpress'); ?></p>
        </div>
    </div>

    <div class="polar-order-edit-content">
        <div class="polar-order-main">
            <!-- Order Information Card -->
            <div class="polar-order-item">
                <div class="order-header">
                    <h4><?php esc_html_e('Order Information', 'pexpress'); ?></h4>
                    <span class="order-status status-<?php echo esc_attr($order_status); ?>">
                        <?php echo esc_html(wc_get_order_status_name($order_status)); ?>
                    </span>
                </div>
                <div class="order-details">
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div class="detail-content">
                                <span class="detail-label"><?php esc_html_e('Date', 'pexpress'); ?></span>
                                <span class="detail-value"><?php echo esc_html($order_date); ?></span>
                            </div>
                        </div>
                        <div class="order-detail-item">
                            <span class="detail-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7 20H17M10 12H14M5 8H19C20.1046 8 21 8.89543 21 10V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V10C3 8.89543 3.89543 8 5 8Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div class="detail-content">
                                <span class="detail-label"><?php esc_html_e('Order number', 'pexpress'); ?></span>
                                <span class="detail-value">#<?php echo esc_html($order_number ? $order_number : $order_id); ?></span>
                            </div>
                        </div>
                        <div class="order-detail-item">
                            <span class="detail-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 8C12.5523 8 13 8.44772 13 9V13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13V9C11 8.44772 11.4477 8 12 8Z"
                                        fill="currentColor" />
                                    <path
                                        d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM4 12C4 7.58172 7.58172 4 12 4C16.4183 4 20 7.58172 20 12C20 16.4183 16.4183 20 12 20C7.58172 20 4 16.4183 4 12Z"
                                        fill="currentColor" />
                                </svg>
                            </span>
                            <div class="detail-content">
                                <span class="detail-label"><?php esc_html_e('Subtotal (actual)', 'pexpress'); ?></span>
                                <span class="detail-value order-subtotal"><?php echo wp_kses_post(wc_price($order_subtotal)); ?></span>
                            </div>
                        </div>
                        <div class="order-detail-item">
                            <span class="detail-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 8C12.5523 8 13 8.44772 13 9V13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13V9C11 8.44772 11.4477 8 12 8Z"
                                        fill="currentColor" />
                                    <path
                                        d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM4 12C4 7.58172 7.58172 4 12 4C16.4183 4 20 7.58172 20 12C20 16.4183 16.4183 20 12 20C7.58172 20 4 16.4183 4 12Z"
                                        fill="currentColor" />
                                </svg>
                            </span>
                            <div class="detail-content">
                                <span class="detail-label"><?php esc_html_e('Total (discounted)', 'pexpress'); ?></span>
                                <span class="detail-value order-total"><?php echo wp_kses_post($order_total); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if ($payment_method_title): ?>
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-label"><?php esc_html_e('Payment method', 'pexpress'); ?></span>
                            <span class="detail-value"><?php echo esc_html($payment_method_title); ?></span>
                        </div>
                        <?php if ($customer_id && $customer_url): ?>
                        <div class="order-detail-item">
                            <span class="detail-label"><?php esc_html_e('Customer account', 'pexpress'); ?></span>
                            <a href="<?php echo esc_url($customer_url); ?>" class="detail-value detail-link" target="_blank" rel="noopener">#<?php echo esc_html($customer_id); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Information Card -->
            <div class="polar-order-item">
                <div class="order-header">
                    <h4><?php esc_html_e('Customer Information', 'pexpress'); ?></h4>
                </div>
                <div class="order-details">
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div class="detail-content">
                                <span class="detail-label"><?php esc_html_e('Customer', 'pexpress'); ?></span>
                                <span class="detail-value customer-name"><?php echo esc_html($customer_name); ?></span>
                            </div>
                        </div>
                        <?php if ($billing_email): ?>
                            <div class="order-detail-item">
                                <span class="detail-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M3 8L10.89 13.26C11.2187 13.4793 11.6049 13.5963 12 13.5963C12.3951 13.5963 12.7813 13.4793 13.11 13.26L21 8M5 19H19C19.5304 19 20.0391 18.7893 20.4142 18.4142C20.7893 18.0391 21 17.5304 21 17V7C21 6.46957 20.7893 5.96086 20.4142 5.58579C20.0391 5.21071 19.5304 5 19 5H5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <div class="detail-content">
                                    <span class="detail-label"><?php esc_html_e('Email', 'pexpress'); ?></span>
                                    <a href="mailto:<?php echo esc_attr($billing_email); ?>"
                                        class="detail-value detail-link">
                                        <?php echo esc_html($billing_email); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="order-detail-row">
                        <?php if ($billing_phone): ?>
                            <div class="order-detail-item">
                                <span class="detail-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <div class="detail-content">
                                    <span class="detail-label"><?php esc_html_e('Phone', 'pexpress'); ?></span>
                                    <a href="tel:<?php echo esc_attr($billing_phone); ?>"
                                        class="detail-value detail-link phone-number">
                                        <?php echo esc_html($billing_phone); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="order-detail-item order-detail-full">
                            <span class="detail-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20 7H4C2.89543 7 2 7.89543 2 9V19C2 20.1046 2.89543 21 4 21H20C21.1046 21 22 20.1046 22 19V9C22 7.89543 21.1046 7 20 7Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M16 21V13C16 11.8954 15.1046 11 14 11H10C8.89543 11 8 11.8954 8 13V21"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div class="detail-content">
                                <span class="detail-label"><?php esc_html_e('Billing address', 'pexpress'); ?></span>
                                <span class="detail-value"><?php echo wp_kses_post(nl2br($billing_address)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Address Card (editable by support) -->
            <?php $is_support = in_array('polar_support', wp_get_current_user()->roles) || current_user_can('manage_woocommerce'); ?>
            <div class="polar-order-item polar-shipping-address-card">
                <div class="order-header">
                    <h4><?php esc_html_e('Shipping Address', 'pexpress'); ?></h4>
                    <?php if ($is_support): ?>
                    <button type="button" class="button button-small polar-edit-shipping-btn" aria-label="<?php esc_attr_e('Edit shipping address', 'pexpress'); ?>">
                        <?php esc_html_e('Edit', 'pexpress'); ?>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="polar-shipping-view">
                    <p class="polar-shipping-display detail-value"><?php echo $shipping_formatted ? wp_kses_post(nl2br($shipping_formatted)) : '<span class="polar-no-address">' . esc_html__('No shipping address provided', 'pexpress') . '</span>'; ?></p>
                </div>
                <?php if ($is_support): ?>
                <div class="polar-shipping-edit-form" style="display: none;">
                    <p class="description" style="margin-bottom: 12px;"><?php esc_html_e('Update the shipping address for this order. Delivery and logistics will use this address.', 'pexpress'); ?></p>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="polar-shipping-first-name"><?php esc_html_e('First name', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-first-name" name="shipping_first_name" value="<?php echo esc_attr($shipping_first_name); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-last-name"><?php esc_html_e('Last name', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-last-name" name="shipping_last_name" value="<?php echo esc_attr($shipping_last_name); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-company"><?php esc_html_e('Company', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-company" name="shipping_company" value="<?php echo esc_attr($shipping_company); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-address-1"><?php esc_html_e('Address line 1', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-address-1" name="shipping_address_1" value="<?php echo esc_attr($shipping_address_1); ?>" class="large-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-address-2"><?php esc_html_e('Address line 2', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-address-2" name="shipping_address_2" value="<?php echo esc_attr($shipping_address_2); ?>" class="large-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-city"><?php esc_html_e('City', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-city" name="shipping_city" value="<?php echo esc_attr($shipping_city); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-state"><?php esc_html_e('State / County', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-state" name="shipping_state" value="<?php echo esc_attr($shipping_state); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-postcode"><?php esc_html_e('Postcode', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-postcode" name="shipping_postcode" value="<?php echo esc_attr($shipping_postcode); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="polar-shipping-country"><?php esc_html_e('Country', 'pexpress'); ?></label></th>
                            <td><input type="text" id="polar-shipping-country" name="shipping_country" value="<?php echo esc_attr($shipping_country); ?>" class="regular-text" placeholder="e.g. BD" /></td>
                        </tr>
                    </table>
                    <p class="polar-shipping-actions">
                        <button type="button" class="button button-primary polar-save-shipping-btn" data-order-id="<?php echo esc_attr($order_id); ?>"><?php esc_html_e('Save address', 'pexpress'); ?></button>
                        <button type="button" class="button polar-cancel-shipping-btn"><?php esc_html_e('Cancel', 'pexpress'); ?></button>
                        <span class="polar-shipping-feedback" role="status" aria-live="polite"></span>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($meeting_type || $meeting_location || $meeting_datetime_display): ?>
            <!-- Meeting Information Card -->
            <div class="polar-order-item">
                <div class="order-header">
                    <h4><?php esc_html_e('Meeting Information', 'pexpress'); ?></h4>
                </div>
                <div class="order-details">
                    <?php if ($meeting_type): ?>
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-label"><?php esc_html_e('Meeting type', 'pexpress'); ?></span>
                            <span class="detail-value"><?php echo esc_html($meeting_type === 'meet_point' ? __('Meet point', 'pexpress') : ($meeting_type === 'delivery_location' ? __('Delivery location', 'pexpress') : esc_html($meeting_type))); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($meeting_location): ?>
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-label"><?php esc_html_e('Meeting location', 'pexpress'); ?></span>
                            <span class="detail-value"><?php echo esc_html($meeting_location); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($meeting_datetime_display): ?>
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-label"><?php esc_html_e('Meeting date & time', 'pexpress'); ?></span>
                            <span class="detail-value"><?php echo esc_html($meeting_datetime_display); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($customer_note): ?>
            <!-- Order Notes (from customer) -->
            <div class="polar-order-item">
                <div class="order-header">
                    <h4><?php esc_html_e('Order note (from customer)', 'pexpress'); ?></h4>
                </div>
                <div class="order-details">
                    <p class="detail-value" style="white-space: pre-wrap;"><?php echo esc_html($customer_note); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($order_confirmed_at || $order_completed_at): ?>
            <!-- Confirmation & completion timestamps -->
            <div class="polar-order-item">
                <div class="order-header">
                    <h4><?php esc_html_e('Status timestamps', 'pexpress'); ?></h4>
                </div>
                <div class="order-details">
                    <?php if ($order_confirmed_at): ?>
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-label"><?php esc_html_e('Order confirmed', 'pexpress'); ?></span>
                            <span class="detail-value"><?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $order_confirmed_at)); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($order_completed_at): ?>
                    <div class="order-detail-row">
                        <div class="order-detail-item">
                            <span class="detail-label"><?php esc_html_e('Order completed', 'pexpress'); ?></span>
                            <span class="detail-value"><?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $order_completed_at)); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Order Items Card -->
            <div class="polar-order-item">
                <div class="order-header">
                    <h4><?php esc_html_e('Order Items', 'pexpress'); ?></h4>
                </div>

                <div class="polar-order-items-table-wrapper">
                    <table class="polar-table polar-table-striped">
                        <thead>
                            <tr>
                                <th class="column-product"><?php esc_html_e('Product', 'pexpress'); ?></th>
                                <th class="column-quantity"><?php esc_html_e('Quantity', 'pexpress'); ?></th>
                                <th class="column-price"><?php esc_html_e('Actual price', 'pexpress'); ?></th>
                                <th class="column-price"><?php esc_html_e('Discounted price', 'pexpress'); ?></th>
                                <th class="column-total"><?php esc_html_e('Total', 'pexpress'); ?></th>
                                <th class="column-actions"><?php esc_html_e('Actions', 'pexpress'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="polar-order-items-tbody">
                            <?php if (!empty($order_items)): ?>
                                <?php
                                // Separate bundle parents from bundled items
                                $bundle_parents = array();
                                $bundled_items = array();
                                $bundled_item_ids = array(); // Track which items are bundled to exclude them from parents

                                // Use WooCommerce Product Bundles helper function if available
                                if (function_exists('wc_pb_get_bundled_order_items')) {
                                    foreach ($order_items as $item_id => $item) {
                                        $product = $item->get_product();

                                        // Check if this is a bundle container using the helper function
                                        if (function_exists('wc_pb_is_bundle_container_order_item')) {
                                            $is_container = wc_pb_is_bundle_container_order_item($item, $order);
                                        } else {
                                            // Fallback: check product type
                                            $is_container = $product && $product->is_type('bundle');
                                        }

                                        if ($is_container) {
                                            $bundle_parents[$item_id] = $item;
                                            // Get bundled items for this bundle using the helper function
                                            $bundled_order_items = wc_pb_get_bundled_order_items($item, $order);
                                            if (!empty($bundled_order_items)) {
                                                $bundled_items[$item_id] = $bundled_order_items;
                                                // Track bundled item IDs
                                                foreach ($bundled_order_items as $bundled_item_id => $bundled_item) {
                                                    $bundled_item_ids[] = $bundled_item_id;
                                                }
                                            }
                                        } elseif (!in_array($item_id, $bundled_item_ids)) {
                                            // Check if this is a bundled item
                                            $bundled_by = $item->get_meta('_bundled_by');
                                            if (!$bundled_by) {
                                                // This is a regular item (not a bundle parent or bundled item)
                                                $bundle_parents[$item_id] = $item;
                                            } else {
                                                // This is a bundled item but parent wasn't found - still track it
                                                $bundled_item_ids[] = $item_id;
                                            }
                                        }
                                    }
                                } else {
                                    // Fallback: manual matching if helper function doesn't exist
                                    // First pass: identify bundle parents and collect their cart keys
                                    $parent_cart_keys = array();
                                    foreach ($order_items as $item_id => $item) {
                                        $bundle_cart_key = $item->get_meta('_bundle_cart_key');
                                        if ($bundle_cart_key) {
                                            // This is a bundle parent
                                            $parent_cart_keys[$bundle_cart_key] = $item_id;
                                            $bundle_parents[$item_id] = $item;
                                        }
                                    }

                                    // Second pass: identify bundled items and link them to parents
                                    foreach ($order_items as $item_id => $item) {
                                        // Skip if already identified as a parent
                                        if (isset($bundle_parents[$item_id])) {
                                            continue;
                                        }

                                        // Check if this is a bundled item
                                        $bundled_by = $item->get_meta('_bundled_by');

                                        if ($bundled_by && isset($parent_cart_keys[$bundled_by])) {
                                            // This is a bundled item - link it to its parent
                                            $parent_id = $parent_cart_keys[$bundled_by];
                                            if (!isset($bundled_items[$parent_id])) {
                                                $bundled_items[$parent_id] = array();
                                            }
                                            $bundled_items[$parent_id][$item_id] = $item;
                                        } else {
                                            // This is a regular item (not a bundle parent or bundled item)
                                            $bundle_parents[$item_id] = $item;
                                        }
                                    }
                                }

                                // Display items: parents first, then their bundled items
                                foreach ($bundle_parents as $item_id => $item):
                                    $product = $item->get_product();
                                    $item_subtotal = (float) $item->get_subtotal();
                                    $item_total = (float) $item->get_total();
                                    $item_quantity = (int) $item->get_quantity();
                                    $unit_price_actual = $item_quantity > 0 ? ($item_subtotal / $item_quantity) : 0;
                                    $unit_price_discounted = $item_quantity > 0 ? ($item_total / $item_quantity) : 0;
                                    $is_bundle = $product && $product->is_type('bundle');
                                    $has_bundled_items = isset($bundled_items[$item_id]) && !empty($bundled_items[$item_id]);
                                ?>
                                    <tr class="polar-order-item-row <?php echo $is_bundle ? 'is-bundle-parent' : ''; ?>"
                                        data-item-id="<?php echo esc_attr($item_id); ?>"
                                        data-quantity="<?php echo esc_attr($item_quantity); ?>"
                                        data-unit-price="<?php echo esc_attr(wc_format_decimal($unit_price_discounted)); ?>"
                                        data-unit-price-actual="<?php echo esc_attr(wc_format_decimal($unit_price_actual)); ?>"
                                        data-line-total="<?php echo esc_attr(wc_format_decimal($item_total)); ?>"
                                        data-line-subtotal="<?php echo esc_attr(wc_format_decimal($item_subtotal)); ?>">
                                        <td class="column-product">
                                            <div class="product-name">
                                                <strong><?php echo esc_html($item->get_name()); ?></strong>
                                                <?php if ($is_bundle): ?>
                                                    <span class="bundle-badge" style="margin-left: 8px; padding: 2px 8px; background: #f0f0f0; border-radius: 3px; font-size: 11px; font-weight: normal;"><?php esc_html_e('Bundle', 'pexpress'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($product && $product->get_sku()): ?>
                                                <div class="product-meta">
                                                    <span class="meta-label">SKU:</span>
                                                    <span class="meta-value"><?php echo esc_html($product->get_sku()); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="column-quantity">
                                            <span class="item-quantity-badge"><?php echo esc_html($item_quantity); ?></span>
                                        </td>
                                        <td class="column-price">
                                            <span class="item-price-actual"><?php echo wc_price($unit_price_actual); ?></span>
                                        </td>
                                        <td class="column-price">
                                            <span class="item-price"><?php echo wc_price($unit_price_discounted); ?></span>
                                        </td>
                                        <td class="column-total">
                                            <strong class="item-total"><?php echo wc_price($item_total); ?></strong>
                                        </td>
                                        <td class="column-actions">
                                            <div class="polar-item-actions">
                                                <button type="button" class="polar-action-btn polar-edit-item"
                                                    data-item-id="<?php echo esc_attr($item_id); ?>"
                                                    title="<?php esc_attr_e('Edit', 'pexpress'); ?>">
                                                    <span class="dashicons dashicons-edit"></span>
                                                </button>
                                                <button type="button" class="polar-action-btn polar-replace-item"
                                                    data-item-id="<?php echo esc_attr($item_id); ?>"
                                                    title="<?php esc_attr_e('Replace', 'pexpress'); ?>">
                                                    <span class="dashicons dashicons-update"></span>
                                                </button>
                                                <button type="button" class="polar-action-btn polar-remove-item destructive"
                                                    data-item-id="<?php echo esc_attr($item_id); ?>"
                                                    title="<?php esc_attr_e('Remove', 'pexpress'); ?>">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                    // Display bundled items for this parent
                                    if ($has_bundled_items):
                                        foreach ($bundled_items[$item_id] as $bundled_item_id => $bundled_item):
                                            $bundled_product = $bundled_item->get_product();
                                            $bundled_item_subtotal = (float) $bundled_item->get_subtotal();
                                            $bundled_item_total = (float) $bundled_item->get_total();
                                            $bundled_item_quantity = (int) $bundled_item->get_quantity();
                                            $bundled_unit_actual = $bundled_item_quantity > 0 ? ($bundled_item_subtotal / $bundled_item_quantity) : 0;
                                            $bundled_unit_discounted = $bundled_item_quantity > 0 ? ($bundled_item_total / $bundled_item_quantity) : 0;
                                    ?>
                                            <tr class="polar-order-item-row is-bundled-item"
                                                data-item-id="<?php echo esc_attr($bundled_item_id); ?>"
                                                data-quantity="<?php echo esc_attr($bundled_item_quantity); ?>"
                                                data-unit-price="<?php echo esc_attr(wc_format_decimal($bundled_unit_discounted)); ?>"
                                                data-unit-price-actual="<?php echo esc_attr(wc_format_decimal($bundled_unit_actual)); ?>"
                                                data-line-total="<?php echo esc_attr(wc_format_decimal($bundled_item_total)); ?>"
                                                data-line-subtotal="<?php echo esc_attr(wc_format_decimal($bundled_item_subtotal)); ?>"
                                                data-parent-item-id="<?php echo esc_attr($item_id); ?>">
                                                <td class="column-product">
                                                    <div class="product-name" style="padding-left: 30px; position: relative;">
                                                        <span style="position: absolute; left: 0; top: 0; bottom: 0; width: 20px; display: flex; align-items: center; color: #999;">
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </span>
                                                        <strong style="font-weight: 500;"><?php echo esc_html($bundled_item->get_name()); ?></strong>
                                                    </div>
                                                    <?php if ($bundled_product && $bundled_product->get_sku()): ?>
                                                        <div class="product-meta" style="padding-left: 30px;">
                                                            <span class="meta-label">SKU:</span>
                                                            <span class="meta-value"><?php echo esc_html($bundled_product->get_sku()); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="column-quantity">
                                                    <span class="item-quantity-badge"><?php echo esc_html($bundled_item_quantity); ?></span>
                                                </td>
                                                <td class="column-price">
                                                    <span class="item-price-actual"><?php echo wc_price($bundled_unit_actual); ?></span>
                                                </td>
                                                <td class="column-price">
                                                    <span class="item-price"><?php echo wc_price($bundled_unit_discounted); ?></span>
                                                </td>
                                                <td class="column-total">
                                                    <strong class="item-total"><?php echo wc_price($bundled_item_total); ?></strong>
                                                </td>
                                                <td class="column-actions">
                                                    <div class="polar-item-actions">
                                                        <button type="button" class="polar-action-btn polar-edit-item"
                                                            data-item-id="<?php echo esc_attr($bundled_item_id); ?>"
                                                            title="<?php esc_attr_e('Edit', 'pexpress'); ?>">
                                                            <span class="dashicons dashicons-edit"></span>
                                                        </button>
                                                        <button type="button" class="polar-action-btn polar-replace-item"
                                                            data-item-id="<?php echo esc_attr($bundled_item_id); ?>"
                                                            title="<?php esc_attr_e('Replace', 'pexpress'); ?>">
                                                            <span class="dashicons dashicons-update"></span>
                                                        </button>
                                                        <button type="button" class="polar-action-btn polar-remove-item destructive"
                                                            data-item-id="<?php echo esc_attr($bundled_item_id); ?>"
                                                            title="<?php esc_attr_e('Remove', 'pexpress'); ?>">
                                                            <span class="dashicons dashicons-trash"></span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                <?php
                                        endforeach;
                                    endif;
                                endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="polar-empty-state">
                                        <div class="empty-state-content">
                                            <span class="dashicons dashicons-cart"></span>
                                            <p><?php esc_html_e('No items in this order.', 'pexpress'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="total-label"><?php esc_html_e('Order Total', 'pexpress'); ?></td>
                                <td colspan="2" class="total-value">
                                    <strong id="polar-order-total"><?php echo wp_kses_post($order_total); ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Inline Add Item Section -->
                <div class="polar-add-item-section polar-add-product-inline-section">
                    <h3><?php esc_html_e('Add Products to Order', 'pexpress'); ?></h3>
                    <p class="polar-add-item-help">
                        <?php esc_html_e('Choose a product and quantity, then add to order.', 'pexpress'); ?>
                    </p>

                    <form class="polar-add-product-form">
                        <table class="widefat polar-modal-products-table polar-add-product-table">
                            <thead>
                                <tr>
                                    <th class="polar-modal-th-product"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                                    <th class="polar-modal-th-quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                                    <th class="polar-modal-th-actions" style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-row-index="0">
                                    <td class="polar-modal-td-product">
                                        <select id="polar-product-dropdown-0" class="polar-product-dropdown" name="item_id">
                                            <option value=""><?php esc_html_e('Select product...', 'pexpress'); ?></option>
                                            <?php foreach ($products_dropdown as $pid => $pname) : ?>
                                                <option value="<?php echo esc_attr($pid); ?>"><?php echo esc_html($pname); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="polar-modal-td-quantity">
                                        <input type="number" id="polar-quantity-0" step="1" min="1" max="9999"
                                            autocomplete="off" name="item_qty" value="1" placeholder="1"
                                            class="quantity polar-modal-quantity-field" style="max-width: 80px;" />
                                    </td>
                                    <td class="polar-modal-td-actions">
                                        <button type="button" class="polar-remove-row-btn" style="display: none;"
                                            title="<?php esc_attr_e('Remove row', 'pexpress'); ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="polar-inline-actions">
                            <button type="button" class="polar-add-row-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <?php esc_html_e('Add Another Product', 'pexpress'); ?>
                            </button>

                            <button type="button"
                                class="button button-primary button-large polar-submit-items-btn polar-btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    style="vertical-align: middle; margin-right: 8px;">
                                    <path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <?php esc_html_e('Add to Order', 'woocommerce'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Forward to Distribution -->
            <div class="polar-order-item polar-forward-card">
                <div class="order-header">
                    <h4><?php esc_html_e('Forward to SR', 'pexpress'); ?></h4>
                    <span class="forward-status-badge <?php echo $is_forwarded ? 'is-forwarded' : 'is-idle'; ?>">
                        <?php echo $is_forwarded ? esc_html__('Forwarded to SR', 'pexpress') : esc_html__('Not Yet Forwarded', 'pexpress'); ?>
                    </span>
                </div>
                <div class="forward-body">
                    <?php if ($forwarded_at_display || $forwarded_by_name): ?>
                        <p class="forward-meta">
                            <?php
                            if ($forwarded_at_display && $forwarded_by_name) {
                                printf(
                                    /* translators: 1: forward date, 2: user name */
                                    esc_html__('Last forwarded on %1$s by %2$s.', 'pexpress'),
                                    esc_html($forwarded_at_display),
                                    esc_html($forwarded_by_name)
                                );
                            } elseif ($forwarded_at_display) {
                                printf(
                                    /* translators: %s: forward date */
                                    esc_html__('Last forwarded on %s.', 'pexpress'),
                                    esc_html($forwarded_at_display)
                                );
                            } elseif ($forwarded_by_name) {
                                printf(
                                    /* translators: %s: user name */
                                    esc_html__('Forwarded by %s.', 'pexpress'),
                                    esc_html($forwarded_by_name)
                                );
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                    <label for="polar-forward-note" class="forward-label">
                        <?php esc_html_e('Support Notes for SR', 'pexpress'); ?>
                    </label>
                    <textarea id="polar-forward-note" class="polar-textarea forward-note" rows="3"
                        placeholder="<?php esc_attr_e('Provide any context SR should know before assignment...', 'pexpress'); ?>"><?php echo esc_textarea($forward_note); ?></textarea>
                    <div class="forward-actions">
                        <button type="button" class="polar-btn polar-btn-primary polar-forward-to-hr"
                            data-order-id="<?php echo esc_attr($order_id); ?>" <?php echo $is_forwarded && !$needs_assignment ? 'disabled' : ''; ?>>
                            <?php echo esc_html(str_replace('HR', 'SR', $forward_button_label)); ?>
                        </button>
                        <?php if ($is_forwarded): ?>
                            <button type="button" class="polar-btn polar-btn-secondary polar-revoke-forward"
                                data-order-id="<?php echo esc_attr($order_id); ?>" style="margin-left: 10px;">
                                <?php esc_html_e('Revoke from SR', 'pexpress'); ?>
                            </button>
                        <?php endif; ?>
                        <span class="polar-forward-feedback" role="status" aria-live="polite"></span>
                    </div>
                </div>
            </div>

            <!-- Order Actions -->
            <?php
            $current_user = wp_get_current_user();
            $is_support = in_array('polar_support', $current_user->roles) || current_user_can('manage_woocommerce');
            $order_confirmed = PExpress_Core::get_order_meta($order_id, '_polar_order_confirmed');
            $order_completed = PExpress_Core::get_order_meta($order_id, '_polar_order_completed');
            ?>
            <?php if ($is_support): ?>
                <div class="polar-order-item polar-order-actions-card">
                    <div class="order-header">
                        <h4><?php esc_html_e('Order Actions', 'pexpress'); ?></h4>
                    </div>
                    <div class="order-actions-body">
                        <?php if (!$order_confirmed): ?>
                            <button type="button" class="polar-btn polar-btn-success polar-confirm-order"
                                data-order-id="<?php echo esc_attr($order_id); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('polar_confirm_order')); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <?php esc_html_e('Confirm Order', 'pexpress'); ?>
                            </button>
                        <?php else: ?>
                            <p class="polar-action-status">
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                <?php esc_html_e('Order Confirmed', 'pexpress'); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!$order_completed && $order->get_status() !== 'completed'): ?>
                            <button type="button" class="polar-btn polar-btn-primary polar-complete-order"
                                data-order-id="<?php echo esc_attr($order_id); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('polar_complete_order')); ?>"
                                style="margin-top: 10px;"
                                <?php echo !$order_confirmed ? 'disabled' : ''; ?>
                                title="<?php echo !$order_confirmed ? esc_attr__('Please confirm the order first', 'pexpress') : ''; ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <?php esc_html_e('Order Completed', 'pexpress'); ?>
                            </button>
                        <?php elseif ($order_completed || $order->get_status() === 'completed'): ?>
                            <p class="polar-action-status" style="margin-top: 10px;">
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                <?php esc_html_e('Order Completed', 'pexpress'); ?>
                            </p>
                        <?php endif; ?>

                        <?php
                        $can_cancel = $order_status !== 'cancelled' && $order_status !== 'completed';
                        ?>
                        <?php if ($can_cancel): ?>
                            <div class="polar-cancel-order-wrap" style="margin-top: 10px;">
                                <button type="button" class="polar-btn polar-btn-danger polar-cancel-order"
                                    data-order-id="<?php echo esc_attr($order_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('polar_cancel_order')); ?>">
                                    <span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                    <?php esc_html_e('Cancel Order', 'pexpress'); ?>
                                </button>
                            </div>
                            <div class="polar-cancel-reason-modal" id="polar-cancel-reason-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100000; align-items: center; justify-content: center;">
                                <div class="polar-cancel-reason-box" style="background: #fff; padding: 24px; border-radius: 8px; max-width: 420px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                                    <h3 style="margin: 0 0 12px 0;"><?php esc_html_e('Cancel Order', 'pexpress'); ?></h3>
                                    <p style="margin: 0 0 12px 0; color: #646970;"><?php esc_html_e('Please provide a reason for cancelling this order.', 'pexpress'); ?></p>
                                    <textarea id="polar-cancel-reason-input" rows="4" class="polar-textarea" style="width: 100%; margin-bottom: 16px; padding: 10px;" placeholder="<?php esc_attr_e('Reason for cancellation...', 'pexpress'); ?>"></textarea>
                                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                        <button type="button" class="button polar-cancel-reason-cancel"><?php esc_html_e('Cancel', 'pexpress'); ?></button>
                                        <button type="button" class="button button-primary polar-cancel-reason-submit"><?php esc_html_e('Submit', 'pexpress'); ?></button>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($order_status === 'cancelled'): ?>
                            <p class="polar-action-status polar-action-cancelled" style="margin-top: 10px;">
                                <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
                                <?php esc_html_e('Order Cancelled', 'pexpress'); ?>
                            </p>
                        <?php endif; ?>
                        <span class="polar-action-feedback" role="status" aria-live="polite"></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Assignments -->
            <?php if ($delivery_id || $fridge_id || $distributor_id): ?>
                <div class="polar-order-item">
                    <div class="order-header">
                        <h4><?php esc_html_e('Assignments', 'pexpress'); ?></h4>
                    </div>
                    <div class="assignment-info">
                        <div class="assignment-badges">
                            <?php if ($delivery_id):
                                $delivery_user = get_userdata($delivery_id);
                            ?>
                                <span class="assignment-badge badge-delivery">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 13L12 20L19 13M12 4V20" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <?php esc_html_e('Delivery:', 'pexpress'); ?>
                                    <?php echo esc_html($delivery_user ? $delivery_user->display_name : 'N/A'); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($fridge_id):
                                $fridge_user = get_userdata($fridge_id);
                            ?>
                                <span class="assignment-badge badge-fridge">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 13L12 20L19 13M12 4V20" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <?php esc_html_e('Fridge Dept (FSD):', 'pexpress'); ?>
                                    <?php echo esc_html($fridge_user ? $fridge_user->display_name : 'N/A'); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($distributor_id):
                                $distributor_user = get_userdata($distributor_id);
                            ?>
                                <span class="assignment-badge badge-distributor">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 13L12 20L19 13M12 4V20" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <?php esc_html_e('Product Provider:', 'pexpress'); ?>
                                    <?php echo esc_html($distributor_user ? $distributor_user->display_name : 'N/A'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stage-wise Order Tracking -->
            <?php if (!empty($stage_wise)): ?>
                <div class="polar-order-item polar-stage-wise-panel">
                    <div class="order-header">
                        <h4><?php esc_html_e('Stage-wise Order Tracking', 'pexpress'); ?></h4>
                    </div>
                    <p class="polar-stage-wise-desc"><?php esc_html_e('Current stage, responsible person, and contact for each role. Use this to contact the correct person.', 'pexpress'); ?></p>
                    <table class="polar-table polar-table-striped polar-stage-wise-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Stage', 'pexpress'); ?></th>
                                <th><?php esc_html_e('Current Status', 'pexpress'); ?></th>
                                <th><?php esc_html_e('Responsible Person', 'pexpress'); ?></th>
                                <th><?php esc_html_e('Contact', 'pexpress'); ?></th>
                                <th><?php esc_html_e('Last Update', 'pexpress'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stage_wise as $row): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($row['stage_label']); ?></strong></td>
                                    <td><span class="status-chip status-<?php echo esc_attr($row['current_status'] === 'pending' ? 'pending' : (in_array($row['current_status'], array('customer_served', 'fridge_returned', 'handoff_complete', 'assigned', 'proceeded'), true) ? 'completed' : 'in-progress')); ?>"><?php echo esc_html($row['status_label']); ?></span></td>
                                    <td><?php echo esc_html($row['responsible_name'] ? $row['responsible_name'] : '—'); ?></td>
                                    <td><?php
                                        if (!empty($row['contact_phone'])) {
                                            echo '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $row['contact_phone'])) . '" class="detail-link">' . esc_html($row['contact_phone']) . '</a>';
                                        } else {
                                            echo '—';
                                        }
                                    ?></td>
                                    <td><?php
                                        if (!empty($row['last_update_timestamp'])) {
                                            echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $row['last_update_timestamp']));
                                            if (!empty($row['last_update_note'])) {
                                                echo ' <span class="polar-update-note">' . esc_html($row['last_update_note']) . '</span>';
                                            }
                                        } else {
                                            echo '—';
                                        }
                                    ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="polar-order-sidebar">
            <!-- Modification History -->
            <div class="polar-order-item">
                <div class="order-header">
                    <h4>
                        <?php esc_html_e('Modification History', 'pexpress'); ?>
                        <span class="polar-toggle-history dashicons dashicons-arrow-down-alt2"></span>
                    </h4>
                </div>
                <div class="polar-history-content is-hidden">
                    <?php if (empty($modification_log)): ?>
                        <p class="polar-empty-state"><?php esc_html_e('No modifications recorded.', 'pexpress'); ?></p>
                    <?php else: ?>
                        <div class="polar-history-list">
                            <?php foreach (array_reverse($modification_log) as $log_entry):
                                $log_action = isset($log_entry['action']) ? $log_entry['action'] : '';
                                $log_timestamp = isset($log_entry['timestamp']) ? $log_entry['timestamp'] : '';
                                $log_user_name = isset($log_entry['user_name']) ? $log_entry['user_name'] : '';
                                $log_old_value = isset($log_entry['old_value']) ? $log_entry['old_value'] : null;
                                $log_new_value = isset($log_entry['new_value']) ? $log_entry['new_value'] : null;
                            ?>
                                <div class="polar-log-entry polar-log-<?php echo esc_attr($log_action); ?>">
                                    <div class="polar-log-header">
                                        <span class="polar-log-time"><?php echo esc_html($log_timestamp); ?></span>
                                        <span class="polar-action-badge polar-action-<?php echo esc_attr($log_action); ?>">
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $log_action ? $log_action : ''))); ?>
                                        </span>
                                    </div>
                                    <div class="polar-log-user">
                                        <?php esc_html_e('By:', 'pexpress'); ?> <?php echo esc_html($log_user_name); ?>
                                    </div>
                                    <?php if (!empty($log_old_value) || !empty($log_new_value)): ?>
                                        <div class="polar-log-details">
                                            <?php
                                            $format_value = function ($value) {
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
                                            };
                                            ?>
                                            <?php if (!empty($log_old_value)): ?>
                                                <div><strong><?php esc_html_e('Before:', 'pexpress'); ?></strong>
                                                    <?php echo wp_kses_post($format_value($log_old_value)); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($log_new_value)): ?>
                                                <div><strong><?php esc_html_e('After:', 'pexpress'); ?></strong>
                                                    <?php echo wp_kses_post($format_value($log_new_value)); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>