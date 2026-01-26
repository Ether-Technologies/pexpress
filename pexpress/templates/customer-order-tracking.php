<?php

/**
 * Customer Order Tracking List Template
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Ensure customer_orders is set
if (!isset($customer_orders) || !is_array($customer_orders)) {
    $customer_orders = array();
}

// Status labels mapping
$status_labels = array(
    'agency' => array(
        'pending' => __('Pending', 'pexpress'),
        'assigned' => __('Assigned', 'pexpress'),
        'proceeded' => __('Proceeded', 'pexpress'),
    ),
    'delivery' => array(
        'pending' => __('Pending', 'pexpress'),
        'meet_point_arrived' => __('Reached Meet Point', 'pexpress'),
        'delivery_location_arrived' => __('Reached Delivery Location', 'pexpress'),
        'service_in_progress' => __('Service In Progress', 'pexpress'),
        'service_complete' => __('Service Completed', 'pexpress'),
        'customer_served' => __('Delivered', 'pexpress'),
    ),
    'fridge' => array(
        'pending' => __('Pending', 'pexpress'),
        'fridge_drop' => __('Fridge Delivered', 'pexpress'),
        'fridge_collected' => __('Fridge Collected', 'pexpress'),
        'fridge_returned' => __('Fridge Returned', 'pexpress'),
    ),
    'distributor' => array(
        'pending' => __('Pending', 'pexpress'),
        'distributor_prep' => __('Preparing', 'pexpress'),
        'out_for_delivery' => __('Out for Delivery', 'pexpress'),
        'handoff_complete' => __('Handoff Complete', 'pexpress'),
    ),
);

// Helper function to get status label
if (!function_exists('pexpress_get_status_label')) {
    function pexpress_get_status_label($role, $status, $labels)
    {
        if (isset($labels[$role][$status])) {
            return $labels[$role][$status];
        }
        return ucfirst(str_replace('_', ' ', $status));
    }
}

// Helper function to get overall order status
if (!function_exists('pexpress_get_overall_status')) {
    function pexpress_get_overall_status($order_id)
    {
        $delivery_status = PExpress_Core::get_role_status($order_id, 'delivery');
        if (empty($delivery_status) || !is_string($delivery_status)) {
            $delivery_status = 'pending';
        }

        if ($delivery_status === 'customer_served') {
            return 'completed';
        } elseif (in_array($delivery_status, array('meet_point_arrived', 'delivery_location_arrived', 'service_in_progress', 'service_complete'), true)) {
            return 'in_progress';
        }
        return 'pending';
    }
}

// Helper function to get order timeline
if (!function_exists('pexpress_get_order_timeline')) {
    function pexpress_get_order_timeline($order_id, $status_labels)
    {
        $timeline = array();

        // Agency status
        $hr_status = PExpress_Core::get_role_status($order_id, 'agency');
        if (empty($hr_status) || !is_string($hr_status)) {
            $hr_status = 'pending';
        }
        $hr_history = PExpress_Core::get_role_status_history($order_id, 'agency');
        if (!empty($hr_history) && is_array($hr_history) && ($hr_status === 'assigned' || $hr_status === 'proceeded')) {
            $last_entry = end($hr_history);
            if (is_array($last_entry) && isset($last_entry['timestamp'])) {
                $time_str = '';
                if (function_exists('mysql2date')) {
                    $time_str = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $last_entry['timestamp']);
                } elseif (!empty($last_entry['timestamp'])) {
                    $time_str = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_entry['timestamp']));
                }
                $timeline[] = array(
                    'icon' => '✓',
                    'title' => pexpress_get_status_label('agency', $hr_status, $status_labels),
                    'time' => $time_str,
                    'completed' => true,
                );
            }
        }

        // Distributor status
        $distributor_status = PExpress_Core::get_role_status($order_id, 'distributor');
        if (empty($distributor_status) || !is_string($distributor_status)) {
            $distributor_status = 'pending';
        }
        $distributor_history = PExpress_Core::get_role_status_history($order_id, 'distributor');
        if (!empty($distributor_history) && is_array($distributor_history) && $distributor_status !== 'pending') {
            $last_entry = end($distributor_history);
            if (is_array($last_entry) && isset($last_entry['timestamp'])) {
                $time_str = '';
                if (function_exists('mysql2date')) {
                    $time_str = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $last_entry['timestamp']);
                } elseif (!empty($last_entry['timestamp'])) {
                    $time_str = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_entry['timestamp']));
                }
                $timeline[] = array(
                    'icon' => '📦',
                    'title' => pexpress_get_status_label('distributor', $distributor_status, $status_labels),
                    'time' => $time_str,
                    'completed' => $distributor_status === 'handoff_complete',
                );
            }
        }

        // Delivery status
        $delivery_status = PExpress_Core::get_role_status($order_id, 'delivery');
        if (empty($delivery_status) || !is_string($delivery_status)) {
            $delivery_status = 'pending';
        }
        $delivery_history = PExpress_Core::get_role_status_history($order_id, 'delivery');
        if (!empty($delivery_history) && is_array($delivery_history) && $delivery_status !== 'pending') {
            $last_entry = end($delivery_history);
            if (is_array($last_entry) && isset($last_entry['timestamp'])) {
                $time_str = '';
                if (function_exists('mysql2date')) {
                    $time_str = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $last_entry['timestamp']);
                } elseif (!empty($last_entry['timestamp'])) {
                    $time_str = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_entry['timestamp']));
                }
                $timeline[] = array(
                    'icon' => '🚚',
                    'title' => pexpress_get_status_label('delivery', $delivery_status, $status_labels),
                    'time' => $time_str,
                    'completed' => $delivery_status === 'customer_served',
                );
            }
        }

        // Fridge status
        $fridge_status = PExpress_Core::get_role_status($order_id, 'fridge');
        if (empty($fridge_status) || !is_string($fridge_status)) {
            $fridge_status = 'pending';
        }
        $fridge_history = PExpress_Core::get_role_status_history($order_id, 'fridge');
        if (!empty($fridge_history) && is_array($fridge_history) && $fridge_status !== 'pending') {
            $last_entry = end($fridge_history);
            if (is_array($last_entry) && isset($last_entry['timestamp'])) {
                $time_str = '';
                if (function_exists('mysql2date')) {
                    $time_str = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $last_entry['timestamp']);
                } elseif (!empty($last_entry['timestamp'])) {
                    $time_str = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_entry['timestamp']));
                }
                $timeline[] = array(
                    'icon' => '🧊',
                    'title' => pexpress_get_status_label('fridge', $fridge_status, $status_labels),
                    'time' => $time_str,
                    'completed' => $fridge_status === 'fridge_returned',
                );
            }
        }

        return $timeline;
    }
}

// Get all unique statuses for filter
$all_statuses = array('all' => __('All Orders', 'pexpress'));
if (!empty($customer_orders) && is_array($customer_orders)) {
    foreach ($customer_orders as $order) {
        if (!$order || !is_a($order, 'WC_Order')) {
            continue;
        }
        $order_id = $order->get_id();
        $overall_status = pexpress_get_overall_status($order_id);
        if (!isset($all_statuses[$overall_status])) {
            $status_label = '';
            if ($overall_status === 'completed') {
                $status_label = __('Completed', 'pexpress');
            } elseif ($overall_status === 'in_progress') {
                $status_label = __('In Progress', 'pexpress');
            } else {
                $status_label = __('Pending', 'pexpress');
            }
            $all_statuses[$overall_status] = $status_label;
        }
    }
}

// Output CSS inline directly in template to ensure it loads
if (defined('PEXPRESS_PLUGIN_DIR')) {
    $css_file_path = PEXPRESS_PLUGIN_DIR . 'assets/css/polar-order-tracking.css';
    if (file_exists($css_file_path)) {
        $css_content = file_get_contents($css_file_path);
        if ($css_content) {
            echo '<style id="polar-order-tracking-inline-css" type="text/css">' . "\n";
            echo $css_content;
            echo '</style>' . "\n";
        }
    }
}

?>
<div class="wrap polar-dashboard polar-customer-tracking">
    <div class="polar-dashboard-header">
        <div class="polar-header-content">
            <h1 class="polar-dashboard-title">
                <span class="polar-title-icon">📦</span>
                <?php esc_html_e('Track My Orders', 'pexpress'); ?>
            </h1>
            <p class="polar-dashboard-subtitle"><?php esc_html_e('View and track all your orders in real-time', 'pexpress'); ?></p>
        </div>
    </div>

    <div class="polar-tracking-filters">
        <div class="polar-filter-row">
            <div class="polar-search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="search-icon">
                    <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <input type="text" id="polar-order-search" class="polar-search-input" placeholder="<?php esc_attr_e('Search by order ID...', 'pexpress'); ?>" />
            </div>
            <div class="polar-status-filter">
                <select id="polar-status-filter" class="polar-filter-select">
                    <?php foreach ($all_statuses as $status_key => $status_label) : ?>
                        <option value="<?php echo esc_attr($status_key); ?>"><?php echo esc_html($status_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="polar-tracking-orders" id="polar-tracking-orders-list">
        <?php if (!empty($customer_orders) && is_array($customer_orders)) : ?>
            <?php foreach ($customer_orders as $order) :
                if (!$order || !is_a($order, 'WC_Order')) {
                    continue;
                }
                $order_id = $order->get_id();
                $order_date = $order->get_date_created();
                $formatted_date = $order_date ? $order_date->date_i18n(get_option('date_format') . ' ' . get_option('time_format')) : '';
                $order_total = $order->get_formatted_order_total();
                $customer_name = PExpress_Core::get_billing_name($order);
                $billing_phone = $order->get_billing_phone();
                $billing_address = $order->get_formatted_billing_address();
                $meeting_type = PExpress_Core::get_meeting_type($order_id);
                $meeting_location = PExpress_Core::get_meeting_location($order_id);
                $meeting_datetime = PExpress_Core::get_meeting_datetime($order_id);
                $meeting_datetime_display = '';
                if (!empty($meeting_datetime)) {
                    $meeting_timestamp = strtotime($meeting_datetime);
                    $meeting_datetime_display = $meeting_timestamp ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $meeting_timestamp) : $meeting_datetime;
                }

                // Get role statuses
                $hr_status = PExpress_Core::get_role_status($order_id, 'agency');
                if (empty($hr_status) || !is_string($hr_status)) {
                    $hr_status = 'pending';
                }
                $delivery_status = PExpress_Core::get_role_status($order_id, 'delivery');
                if (empty($delivery_status) || !is_string($delivery_status)) {
                    $delivery_status = 'pending';
                }
                $fridge_status = PExpress_Core::get_role_status($order_id, 'fridge');
                if (empty($fridge_status) || !is_string($fridge_status)) {
                    $fridge_status = 'pending';
                }
                $distributor_status = PExpress_Core::get_role_status($order_id, 'distributor');
                if (empty($distributor_status) || !is_string($distributor_status)) {
                    $distributor_status = 'pending';
                }

                $overall_status = pexpress_get_overall_status($order_id);
                $timeline = pexpress_get_order_timeline($order_id, $status_labels);

                // Get current status info
                $current_status_text = '';
                $current_status_icon = '';
                if ($overall_status === 'completed') {
                    $current_status_text = __('Delivered', 'pexpress');
                    $current_status_icon = '✓';
                } elseif ($overall_status === 'in_progress') {
                    if ($distributor_status === 'distributor_prep') {
                        $current_status_text = __('Preparing', 'pexpress');
                        $current_status_icon = '👨‍🍳';
                    } elseif ($delivery_status === 'service_in_progress') {
                        $current_status_text = __('On the way', 'pexpress');
                        $current_status_icon = '🚚';
                    } else {
                        $current_status_text = __('In Progress', 'pexpress');
                        $current_status_icon = '⏳';
                    }
                } else {
                    // Check if order is confirmed by support
                    // Must explicitly check for non-empty string/date value
                    $order_confirmed_meta = PExpress_Core::get_order_meta($order_id, '_polar_order_confirmed');
                    $order_confirmed = ($order_confirmed_meta !== false && $order_confirmed_meta !== '' && $order_confirmed_meta !== null && trim($order_confirmed_meta) !== '');
                    if ($order_confirmed) {
                        $current_status_text = __('Confirmed', 'pexpress');
                        $current_status_icon = '✓';
                    } else {
                        $current_status_text = __('Order Placed', 'pexpress');
                        $current_status_icon = '📝';
                    }
                }

                // Get order view URL
                $order_view_url = wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount'));

                // Check if order is confirmed by support (check once, use everywhere)
                // Must explicitly check for non-empty string/date value
                $order_confirmed_meta = PExpress_Core::get_order_meta($order_id, '_polar_order_confirmed');
                $order_confirmed = ($order_confirmed_meta !== false && $order_confirmed_meta !== '' && $order_confirmed_meta !== null && trim($order_confirmed_meta) !== '');

                // Get confirmed date (from support confirmation, not agency assignment)
                $confirmed_date = '';
                if ($order_confirmed) {
                    if (function_exists('mysql2date')) {
                        $confirmed_date = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $order_confirmed_meta);
                    } else {
                        $confirmed_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order_confirmed_meta));
                    }
                }
                // Fallback to order date if not confirmed yet
                if (empty($confirmed_date)) {
                    $confirmed_date = $formatted_date;
                }

                // Define order stages for horizontal progress bar
                $stages = array(
                    array(
                        'key' => 'placed',
                        'label' => __('Order Placed', 'pexpress'),
                        'icon' => '📝',
                        'completed' => true, // Always completed once order exists
                    ),
                    array(
                        'key' => 'confirmed',
                        'label' => __('Order Confirmed', 'pexpress'),
                        'icon' => '✓',
                        'completed' => $order_confirmed, // Only completed when support confirms
                    ),
                    array(
                        'key' => 'preparing',
                        'label' => __('Preparing', 'pexpress'),
                        'icon' => '📦',
                        'completed' => ($distributor_status !== 'pending'),
                    ),
                    array(
                        'key' => 'in_transit',
                        'label' => __('In Transit', 'pexpress'),
                        'icon' => '🚚',
                        'completed' => (in_array($delivery_status, array('meet_point_arrived', 'delivery_location_arrived', 'service_in_progress', 'service_complete', 'customer_served'), true)),
                    ),
                    array(
                        'key' => 'delivered',
                        'label' => __('Delivered', 'pexpress'),
                        'icon' => '🏠',
                        'completed' => ($delivery_status === 'customer_served'),
                    ),
                );

                // Determine current active stage
                // Special logic: If order is not confirmed, keep "Order Placed" as active
                // Otherwise, find first incomplete stage
                $active_stage_index = 0;
                if (!$order_confirmed) {
                    // Order not confirmed yet - "Order Placed" should be active
                    $active_stage_index = 0;
                } else {
                    // Order confirmed - find first incomplete stage
                    foreach ($stages as $index => $stage) {
                        if (!$stage['completed']) {
                            $active_stage_index = $index;
                            break;
                        }
                    }
                    // If all stages are completed, mark the last one as active
                    $all_completed = true;
                    foreach ($stages as $stage) {
                        if (!$stage['completed']) {
                            $all_completed = false;
                            break;
                        }
                    }
                    if ($all_completed && count($stages) > 0) {
                        $active_stage_index = count($stages) - 1;
                    }
                }
            ?>
                <a href="<?php echo esc_url($order_view_url); ?>" class="polar-order-card-link">
                    <div class="polar-order-card" data-order-id="<?php echo esc_attr($order_id); ?>" data-status="<?php echo esc_attr($overall_status); ?>" data-search-text="<?php echo esc_attr(strtolower($order_id . ' ' . $customer_name . ' ' . $billing_phone)); ?>">
                        <div class="polar-order-card-header">
                            <div class="polar-order-header-left">
                                <div class="polar-order-number">
                                    <span class="order-number-label"><?php esc_html_e('ORDER', 'pexpress'); ?></span>
                                    <span class="order-number-value">#<?php echo esc_html($order_id); ?></span>
                                </div>
                            </div>
                            <div class="polar-order-header-right">
                                <?php if ($overall_status === 'completed' && $delivery_status === 'customer_served') : ?>
                                    <?php
                                    $delivery_history = PExpress_Core::get_role_status_history($order_id, 'delivery');
                                    $delivered_time = '';
                                    if (!empty($delivery_history) && is_array($delivery_history)) {
                                        foreach (array_reverse($delivery_history) as $entry) {
                                            if (is_array($entry) && isset($entry['status']) && $entry['status'] === 'customer_served') {
                                                if (isset($entry['timestamp']) && !empty($entry['timestamp'])) {
                                                    if (function_exists('mysql2date')) {
                                                        $delivered_time = mysql2date(get_option('date_format'), $entry['timestamp']);
                                                    } else {
                                                        $delivered_time = date_i18n(get_option('date_format'), strtotime($entry['timestamp']));
                                                    }
                                                }
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="polar-expected-arrival">
                                        <?php esc_html_e('Delivered', 'pexpress'); ?>
                                        <?php if (!empty($delivered_time)) : ?>
                                            <span class="arrival-date"><?php echo esc_html($delivered_time); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($meeting_datetime_display) : ?>
                                    <div class="polar-expected-arrival">
                                        <?php esc_html_e('Expected Arrival', 'pexpress'); ?>
                                        <span class="arrival-date"><?php echo esc_html($meeting_datetime_display); ?></span>
                                    </div>
                                <?php else : ?>
                                    <div class="polar-expected-arrival">
                                        <?php esc_html_e('Order Date', 'pexpress'); ?>
                                        <span class="arrival-date"><?php echo esc_html($formatted_date); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="polar-progress-bar-container">
                            <div class="polar-progress-bar">
                                <?php foreach ($stages as $index => $stage) : ?>
                                    <div class="polar-progress-stage <?php echo $stage['completed'] ? 'completed' : ($index === $active_stage_index ? 'active' : ''); ?>">
                                        <div class="polar-progress-milestone">
                                            <div class="polar-progress-node">
                                                <?php if ($stage['completed']) : ?>
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="check-icon">
                                                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                <?php else : ?>
                                                    <span class="stage-icon"><?php echo esc_html($stage['icon']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="polar-progress-label"><?php echo esc_html($stage['label']); ?></div>
                                        </div>
                                        <?php if ($index < count($stages) - 1) : ?>
                                            <div class="polar-progress-line <?php echo $stage['completed'] ? 'completed' : ''; ?>"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="polar-empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3><?php esc_html_e('No orders found', 'pexpress'); ?></h3>
                <p><?php esc_html_e('You don\'t have any orders yet.', 'pexpress'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="polar-tracking-footer">
        <div class="polar-auto-refresh-indicator">
            <span class="polar-refresh-icon">🔄</span>
            <span class="polar-refresh-text"><?php esc_html_e('Auto-refreshing every 15 seconds...', 'pexpress'); ?></span>
        </div>
    </div>
</div>