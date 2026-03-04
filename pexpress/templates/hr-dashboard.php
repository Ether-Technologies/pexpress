<?php

/**
 * Agent Dashboard Template
 * This template is exclusively for Agent Dashboard functionality.
 * It only displays Agent Dashboard content, regardless of user roles.
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php
// Calculate stats
$pending_count = count($pending_orders);
$assigned_in_progress_orders = isset($assigned_in_progress_orders) ? $assigned_in_progress_orders : array();
$completed_orders = isset($completed_orders) ? $completed_orders : array();
$assigned_count = count($assigned_in_progress_orders);
$completed_count = count($completed_orders);
// Agent Dashboard only uses hr_users (SR Personnel)
$hr_count = isset($hr_users) ? count($hr_users) : 0;
$fridge_count = count($fridge_users);
$distributor_count = count($distributor_users);
?>

<div class="wrap polar-dashboard polar-hr-dashboard">
    <div class="polar-dashboard-header">
        <div class="polar-header-content">
            <h1 class="polar-dashboard-title">
                <span class="polar-title-icon">👥</span>
                <?php esc_html_e('Agent Dashboard - Task Assignment', 'pexpress'); ?>
            </h1>
            <p class="polar-dashboard-subtitle"><?php esc_html_e('Assign orders to SR, fridge, and product provider teams', 'pexpress'); ?></p>
        </div>
    </div>

    <div class="polar-stats-grid">
        <div class="polar-stat-card stat-card-primary">
            <div class="stat-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value"><?php echo esc_html($pending_count); ?></h3>
                <p class="stat-card-label"><?php esc_html_e('Orders Pending Assignment', 'pexpress'); ?></p>
            </div>
        </div>
        <div class="polar-stat-card stat-card-warning">
            <div class="stat-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88M13 7C13 9.20914 10.2091 11 8 11C5.79086 11 3 9.20914 3 7C3 4.79086 5.79086 3 8 3C10.2091 3 13 4.79086 13 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value"><?php echo esc_html($hr_count); ?></h3>
                <p class="stat-card-label"><?php esc_html_e('SR Personnel', 'pexpress'); ?></p>
            </div>
        </div>
        <div class="polar-stat-card stat-card-success">
            <div class="stat-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88M13 7C13 9.20914 10.2091 11 8 11C5.79086 11 3 9.20914 3 7C3 4.79086 5.79086 3 8 3C10.2091 3 13 4.79086 13 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value"><?php echo esc_html($fridge_count); ?></h3>
                <p class="stat-card-label"><?php esc_html_e('Fridge Dept (FSD)', 'pexpress'); ?></p>
            </div>
        </div>
        <div class="polar-stat-card stat-card-info">
            <div class="stat-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88M13 7C13 9.20914 10.2091 11 8 11C5.79086 11 3 9.20914 3 7C3 4.79086 5.79086 3 8 3C10.2091 3 13 4.79086 13 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value"><?php echo esc_html($distributor_count); ?></h3>
                <p class="stat-card-label"><?php esc_html_e('Product Providers', 'pexpress'); ?></p>
            </div>
        </div>
    </div>

    <div class="polar-orders-section">
        <div class="polar-section-header">
            <h2 class="polar-section-title"><?php esc_html_e('Orders', 'pexpress'); ?></h2>
        </div>

        <div class="polar-tabs">
            <button class="polar-tab active" data-tab="pending"><?php esc_html_e('Pending Assignment', 'pexpress'); ?> (<?php echo esc_html($pending_count); ?>)</button>
            <button class="polar-tab" data-tab="in-progress"><?php esc_html_e('In Progress', 'pexpress'); ?> (<?php echo esc_html($assigned_count); ?>)</button>
            <button class="polar-tab" data-tab="completed"><?php esc_html_e('Completed', 'pexpress'); ?> (<?php echo esc_html($completed_count); ?>)</button>
        </div>

        <div class="polar-filters-wrapper">
            <div class="polar-filters">
                <div class="polar-filter-group">
                    <label class="polar-filter-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 4H21M7 8H17M10 12H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        <?php esc_html_e('Filter', 'pexpress'); ?>
                    </label>
                    <select id="polar-agency-status-filter" class="polar-select">
                        <option value=""><?php esc_html_e('All Statuses', 'pexpress'); ?></option>
                        <?php
                        $agency_statuses = wc_get_order_statuses();
                        if (is_array($agency_statuses)) {
                            foreach ($agency_statuses as $status_key => $status_label) {
                                if (empty($status_key) || empty($status_label)) continue;
                                echo '<option value="' . esc_attr($status_key) . '">' . esc_html($status_label) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="polar-filter-group polar-search-group">
                    <label class="polar-filter-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <?php esc_html_e('Search', 'pexpress'); ?>
                    </label>
                    <input type="text" id="polar-agency-search" class="polar-input" placeholder="<?php esc_attr_e('Search by order ID, customer name, or phone...', 'pexpress'); ?>">
                </div>
            </div>
        </div>

        <div class="polar-tab-content active" id="tab-pending">
            <?php if (!empty($pending_orders)) : ?>
                <div class="polar-orders-list" id="polar-orders-list">
                    <?php foreach ($pending_orders as $order) :
                        $order_id = $order->get_id();
                        $forwarded_by_id = (int) PExpress_Core::get_order_meta($order_id, '_polar_forwarded_by');
                        $forwarded_at_raw = PExpress_Core::get_order_meta($order_id, '_polar_forwarded_at');
                        $forwarded_note = PExpress_Core::get_order_meta($order_id, '_polar_forward_note');
                        $forwarded_by_user = $forwarded_by_id ? get_userdata($forwarded_by_id) : false;
                        $forwarded_by_name = $forwarded_by_user ? $forwarded_by_user->display_name : '';
                        $forwarded_at = $forwarded_at_raw ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $forwarded_at_raw) : '';
                        $meeting_type = PExpress_Core::get_meeting_type($order_id);
                        $meeting_location = PExpress_Core::get_meeting_location($order_id);
                        $meeting_datetime = PExpress_Core::get_meeting_datetime($order_id);
                        $meeting_datetime_value = '';
                        if (!empty($meeting_datetime)) {
                            $meeting_timestamp = strtotime($meeting_datetime);
                            if ($meeting_timestamp) {
                                $meeting_datetime_value = gmdate('Y-m-d\TH:i', $meeting_timestamp);
                            } else {
                                $meeting_datetime_value = $meeting_datetime;
                            }
                        }
                        $meeting_datetime_display = '';
                        if (!empty($meeting_datetime)) {
                            $meeting_ts = strtotime($meeting_datetime);
                            $meeting_datetime_display = $meeting_ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $meeting_ts) : $meeting_datetime;
                        }
                        $fridge_asset_id = PExpress_Core::get_fridge_asset_id($order_id);
                        $fridge_return_date = PExpress_Core::get_order_meta($order_id, '_polar_fridge_return_date');
                        $fridge_return_datetime_value = '';
                        if (!empty($fridge_return_date)) {
                            $fridge_timestamp = strtotime($fridge_return_date);
                            if ($fridge_timestamp) {
                                // Support both date-only and datetime: prefill datetime-local as Y-m-d\TH:i
                                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($fridge_return_date))) {
                                    $fridge_return_datetime_value = gmdate('Y-m-d', $fridge_timestamp) . 'T00:00';
                                } else {
                                    $fridge_return_datetime_value = gmdate('Y-m-d\TH:i', $fridge_timestamp);
                                }
                            }
                        }
                        $delivery_instructions = PExpress_Core::get_role_instructions($order_id, 'delivery');
                        $fridge_instructions = PExpress_Core::get_role_instructions($order_id, 'fridge');
                        $distributor_instructions = PExpress_Core::get_role_instructions($order_id, 'distributor');
                    ?>
                        <div class="polar-order-item" data-order-id="<?php echo esc_attr($order_id); ?>" data-status="<?php echo esc_attr($order->get_status()); ?>">
                            <div class="order-header">
                                <div class="order-header-left">
                                    <h4 class="order-title">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" target="_blank" class="order-link">
                                            <span class="order-id-badge">#<?php echo esc_html($order_id); ?></span>
                                        </a>
                                    </h4>
                                    <span class="order-date-badge">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <?php echo esc_html($order->get_date_created()->date_i18n('M d, Y')); ?>
                                    </span>
                                </div>
                                <span class="order-status status-pending">
                                    <?php esc_html_e('Pending', 'pexpress'); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <div class="order-detail-row">
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Customer', 'pexpress'); ?></span>
                                            <span class="detail-value customer-name"><?php echo esc_html(PExpress_Core::get_billing_name($order)); ?></span>
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Phone', 'pexpress'); ?></span>
                                            <a href="tel:<?php echo esc_attr($order->get_billing_phone()); ?>" class="detail-value detail-link phone-number">
                                                <?php echo esc_html($order->get_billing_phone()); ?>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Event Date & Time', 'pexpress'); ?></span>
                                            <span class="detail-value"><?php echo $meeting_datetime_display ? esc_html($meeting_datetime_display) : '—'; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-detail-row">
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 8L10.89 13.26C11.2187 13.4793 11.6049 13.5963 12 13.5963C12.3951 13.5963 12.7813 13.4793 13.11 13.26L21 8M5 19H19C19.5304 19 20.0391 18.7893 20.4142 18.4142C20.7893 18.0391 21 17.5304 21 17V7C21 6.46957 20.7893 5.96086 20.4142 5.58579C20.0391 5.21071 19.5304 5 19 5H5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Email', 'pexpress'); ?></span>
                                            <a href="mailto:<?php echo esc_attr($order->get_billing_email()); ?>" class="detail-value detail-link">
                                                <?php echo esc_html($order->get_billing_email()); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($meeting_type || $meeting_location) : ?>
                                    <div class="order-detail-row">
                                        <?php if ($meeting_type) : ?>
                                            <div class="order-detail-item">
                                                <div class="detail-content">
                                                    <span class="detail-label"><?php esc_html_e('Meeting Type', 'pexpress'); ?></span>
                                                    <span class="detail-value"><?php echo esc_html($meeting_type === 'meet_point' ? __('Meet Point', 'pexpress') : ($meeting_type === 'delivery_location' ? __('Delivery Location', 'pexpress') : $meeting_type)); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($meeting_location) : ?>
                                            <div class="order-detail-item order-detail-full">
                                                <div class="detail-content">
                                                    <span class="detail-label"><?php esc_html_e('Meeting Location', 'pexpress'); ?></span>
                                                    <span class="detail-value"><?php echo esc_html($meeting_location); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="assignment-form">
                                <form class="polar-assign-form" method="post" data-order-id="<?php echo esc_attr($order_id); ?>">
                                    <?php wp_nonce_field('polar_assign_' . $order_id, 'polar_assign_nonce'); ?>
                                    <input type="hidden" name="action" value="polar_assign_order">
                                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">

                                    <div class="assign-row">
                                        <div class="assign-field">
                                            <label><?php esc_html_e('Meeting Type:', 'pexpress'); ?></label>
                                            <select name="meeting_type" class="polar-select">
                                                <option value="meet_point" <?php selected('meet_point', $meeting_type); ?>>
                                                    <?php esc_html_e('Meet Point', 'pexpress'); ?>
                                                </option>
                                                <option value="delivery_location" <?php selected('delivery_location', $meeting_type); ?>>
                                                    <?php esc_html_e('Delivery Location', 'pexpress'); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="assign-field polar-meeting-location-field" style="<?php echo ($meeting_type === 'delivery_location') ? 'display: none;' : ''; ?>">
                                            <label><?php esc_html_e('Meeting Address / Notes:', 'pexpress'); ?></label>
                                            <input type="text" name="meeting_location" class="polar-input" value="<?php echo esc_attr($meeting_location); ?>" placeholder="<?php esc_attr_e('Enter meet point address', 'pexpress'); ?>">
                                        </div>
                                        <div class="assign-field">
                                            <label><?php esc_html_e('Meeting Date & Time:', 'pexpress'); ?></label>
                                            <input type="datetime-local" name="meeting_datetime" class="polar-input" value="<?php echo esc_attr($meeting_datetime_value); ?>">
                                        </div>
                                    </div>

                                    <div class="assign-row">
                                        <div class="assign-field">
                                            <label><?php esc_html_e('SR Person:', 'pexpress'); ?></label>
                                            <select name="delivery_user_id" class="polar-select">
                                                <option value=""><?php esc_html_e('Select...', 'pexpress'); ?></option>
                                                <?php
                                                // Agent Dashboard only uses hr_users (SR Personnel)
                                                $users_for_select = isset($hr_users) ? $hr_users : array();
                                                foreach ($users_for_select as $user) : ?>
                                                    <option value="<?php echo esc_attr($user->ID); ?>">
                                                        <?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_email); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="assign-field">
                                            <label><?php esc_html_e('Fridge Provider:', 'pexpress'); ?></label>
                                            <select name="fridge_user_id" class="polar-select">
                                                <option value=""><?php esc_html_e('Select...', 'pexpress'); ?></option>
                                                <?php foreach ($fridge_users as $user) : ?>
                                                    <option value="<?php echo esc_attr($user->ID); ?>">
                                                        <?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_email); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="assign-field">
                                            <label><?php esc_html_e('Product Provider:', 'pexpress'); ?></label>
                                            <select name="distributor_user_id" class="polar-select">
                                                <option value=""><?php esc_html_e('Select...', 'pexpress'); ?></option>
                                                <?php foreach ($distributor_users as $user) : ?>
                                                    <option value="<?php echo esc_attr($user->ID); ?>">
                                                        <?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_email); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="assign-row">
                                        <div class="assign-field">
                                            <label><?php esc_html_e('Fridge Return Date & Time:', 'pexpress'); ?></label>
                                            <input type="datetime-local" name="fridge_return_date" class="polar-input" value="<?php echo esc_attr($fridge_return_datetime_value); ?>">
                                        </div>
                                        <div class="assign-field">
                                            <label><?php esc_html_e('Fridge Asset ID:', 'pexpress'); ?></label>
                                            <input type="text" name="fridge_asset_id" class="polar-input" value="<?php echo esc_attr($fridge_asset_id); ?>" placeholder="<?php esc_attr_e('e.g., FR-01', 'pexpress'); ?>">
                                        </div>
                                    </div>

                                    <div class="assign-row">
                                        <div class="assign-field assign-field-full">
                                            <label><?php esc_html_e('Assignment Notes:', 'pexpress'); ?></label>
                                            <textarea name="assignment_note" class="polar-textarea" rows="2" placeholder="<?php esc_attr_e('Add any special instructions...', 'pexpress'); ?>"></textarea>
                                        </div>
                                    </div>

                                    <div class="assign-row">
                                        <div class="assign-field assign-field-full">
                                            <label><?php esc_html_e('SR Instructions:', 'pexpress'); ?></label>
                                            <textarea name="delivery_instructions" class="polar-textarea" rows="2" placeholder="<?php esc_attr_e('Guidance for SR team...', 'pexpress'); ?>"><?php echo esc_textarea($delivery_instructions); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="assign-row">
                                        <div class="assign-field assign-field-full">
                                            <label><?php esc_html_e('Fridge Instructions:', 'pexpress'); ?></label>
                                            <textarea name="fridge_instructions" class="polar-textarea" rows="2" placeholder="<?php esc_attr_e('Guidance for fridge provider...', 'pexpress'); ?>"><?php echo esc_textarea($fridge_instructions); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="assign-row">
                                        <div class="assign-field assign-field-full">
                                            <label><?php esc_html_e('Product Provider Instructions:', 'pexpress'); ?></label>
                                            <textarea name="distributor_instructions" class="polar-textarea" rows="2" placeholder="<?php esc_attr_e('Guidance for product provider...', 'pexpress'); ?>"><?php echo esc_textarea($distributor_instructions); ?></textarea>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php
                                        // Agent Dashboard: Check if order is already proceeded
                                        $order_proceeded = PExpress_Core::get_order_meta($order_id, '_polar_order_proceeded');
                                        $agency_status = PExpress_Core::get_role_status($order_id, 'agency');
                                        ?>
                                        <?php if ($order_proceeded || $agency_status === 'proceeded') : ?>
                                            <span class="polar-action-status" style="display: inline-flex; align-items: center; color: #46b450;">
                                                <span class="dashicons dashicons-yes-alt" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                                <?php esc_html_e('Assigned & Proceeded', 'pexpress'); ?>
                                            </span>
                                        <?php else : ?>
                                            <button type="submit" class="polar-btn polar-btn-primary">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 6px; vertical-align: middle;">
                                                    <path d="M13 10V3L4 14H11V21L20 10H13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <?php esc_html_e('Assign & Proceed Order', 'pexpress'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <span class="polar-assign-loading" style="display:none;"><?php esc_html_e('Assigning...', 'pexpress'); ?></span>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="polar-empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3><?php esc_html_e('No orders need assignment', 'pexpress'); ?></h3>
                    <p><?php esc_html_e('No orders need assignment at this time.', 'pexpress'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="polar-tab-content" id="tab-in-progress">
            <?php if (!empty($assigned_in_progress_orders)) : ?>
                <div class="polar-orders-list" id="polar-orders-list-in-progress">
                    <?php foreach ($assigned_in_progress_orders as $order) :
                        $order_id = $order->get_id();
                        $meeting_type = PExpress_Core::get_meeting_type($order_id);
                        $meeting_location = PExpress_Core::get_meeting_location($order_id);
                        $meeting_datetime = PExpress_Core::get_meeting_datetime($order_id);
                        $meeting_datetime_display = '';
                        if (!empty($meeting_datetime)) {
                            $meeting_ts = strtotime($meeting_datetime);
                            $meeting_datetime_display = $meeting_ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $meeting_ts) : $meeting_datetime;
                        }
                        $delivery_id = PExpress_Core::get_delivery_user_id($order_id);
                        $fridge_id = PExpress_Core::get_fridge_user_id($order_id);
                        $distributor_id = PExpress_Core::get_distributor_user_id($order_id);
                    ?>
                        <div class="polar-order-item" data-order-id="<?php echo esc_attr($order_id); ?>" data-status="<?php echo esc_attr($order->get_status()); ?>">
                            <div class="order-header">
                                <div class="order-header-left">
                                    <h4 class="order-title">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" target="_blank" class="order-link">
                                            <span class="order-id-badge">#<?php echo esc_html($order_id); ?></span>
                                        </a>
                                    </h4>
                                    <span class="order-date-badge">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <?php echo esc_html($order->get_date_created()->date_i18n('M d, Y')); ?>
                                    </span>
                                </div>
                                <span class="order-status status-<?php echo esc_attr($order->get_status()); ?>">
                                    <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <div class="order-detail-row">
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Customer', 'pexpress'); ?></span>
                                            <span class="detail-value customer-name"><?php echo esc_html(PExpress_Core::get_billing_name($order)); ?></span>
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Phone', 'pexpress'); ?></span>
                                            <a href="tel:<?php echo esc_attr($order->get_billing_phone()); ?>" class="detail-value detail-link phone-number"><?php echo esc_html($order->get_billing_phone()); ?></a>
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Event Date & Time', 'pexpress'); ?></span>
                                            <span class="detail-value"><?php echo $meeting_datetime_display ? esc_html($meeting_datetime_display) : '—'; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-detail-row">
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 8L10.89 13.26C11.2187 13.4793 11.6049 13.5963 12 13.5963C12.3951 13.5963 12.7813 13.4793 13.11 13.26L21 8M5 19H19C19.5304 19 20.0391 18.7893 20.4142 18.4142C20.7893 18.0391 21 17.5304 21 17V7C21 6.46957 20.7893 5.96086 20.4142 5.58579C20.0391 5.21071 19.5304 5 19 5H5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Email', 'pexpress'); ?></span>
                                            <a href="mailto:<?php echo esc_attr($order->get_billing_email()); ?>" class="detail-value detail-link"><?php echo esc_html($order->get_billing_email()); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($meeting_type || $meeting_location) : ?>
                                    <div class="order-detail-row">
                                        <?php if ($meeting_type) : ?>
                                            <div class="order-detail-item">
                                                <div class="detail-content">
                                                    <span class="detail-label"><?php esc_html_e('Meeting Type', 'pexpress'); ?></span>
                                                    <span class="detail-value"><?php echo esc_html($meeting_type === 'meet_point' ? __('Meet Point', 'pexpress') : ($meeting_type === 'delivery_location' ? __('Delivery Location', 'pexpress') : $meeting_type)); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($meeting_location) : ?>
                                            <div class="order-detail-item order-detail-full">
                                                <div class="detail-content">
                                                    <span class="detail-label"><?php esc_html_e('Meeting Location', 'pexpress'); ?></span>
                                                    <span class="detail-value"><?php echo esc_html($meeting_location); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="order-actions" style="margin-top: 10px;">
                                <button type="button"
                                    style="background-color: var(--polar-primary); color: #fff; border-radius: var(--polar-radius-md);"
                                    class="polar-btn polar-btn-secondary polar-view-tracking-btn" data-order-id="<?php echo esc_attr($order_id); ?>"><?php esc_html_e('View Tracking', 'pexpress'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="polar-empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3><?php esc_html_e('No orders in progress', 'pexpress'); ?></h3>
                    <p><?php esc_html_e('No orders in progress. Orders that are no longer pending assignment appear here.', 'pexpress'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="polar-tab-content" id="tab-completed">
            <?php if (!empty($completed_orders)) : ?>
                <div class="polar-orders-list">
                    <?php foreach ($completed_orders as $order) :
                        $order_id = $order->get_id();
                        $meeting_datetime = PExpress_Core::get_meeting_datetime($order_id);
                        $meeting_datetime_display = '';
                        if (!empty($meeting_datetime)) {
                            $meeting_ts = strtotime($meeting_datetime);
                            $meeting_datetime_display = $meeting_ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $meeting_ts) : $meeting_datetime;
                        }
                        $meeting_type = PExpress_Core::get_meeting_type($order_id);
                        $meeting_location = PExpress_Core::get_meeting_location($order_id);
                        $delivery_id = PExpress_Core::get_delivery_user_id($order_id);
                        $fridge_id = PExpress_Core::get_fridge_user_id($order_id);
                        $distributor_id = PExpress_Core::get_distributor_user_id($order_id);
                    ?>
                        <div class="polar-order-item" data-order-id="<?php echo esc_attr($order_id); ?>" data-status="<?php echo esc_attr($order->get_status()); ?>">
                            <div class="order-header">
                                <div class="order-header-left">
                                    <h4 class="order-title">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" target="_blank" class="order-link">
                                            <span class="order-id-badge">#<?php echo esc_html($order_id); ?></span>
                                        </a>
                                    </h4>
                                    <span class="order-date-badge">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <?php echo esc_html($order->get_date_created()->date_i18n('M d, Y')); ?>
                                    </span>
                                </div>
                                <span class="order-status status-<?php echo esc_attr($order->get_status()); ?>">
                                    <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <div class="order-detail-row">
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Customer', 'pexpress'); ?></span>
                                            <span class="detail-value customer-name"><?php echo esc_html(PExpress_Core::get_billing_name($order)); ?></span>
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Phone', 'pexpress'); ?></span>
                                            <a href="tel:<?php echo esc_attr($order->get_billing_phone()); ?>" class="detail-value detail-link phone-number"><?php echo esc_html($order->get_billing_phone()); ?></a>
                                        </div>
                                    </div>
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Event Date & Time', 'pexpress'); ?></span>
                                            <span class="detail-value"><?php echo $meeting_datetime_display ? esc_html($meeting_datetime_display) : '—'; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-detail-row">
                                    <div class="order-detail-item">
                                        <span class="detail-icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 8L10.89 13.26C11.2187 13.4793 11.6049 13.5963 12 13.5963C12.3951 13.5963 12.7813 13.4793 13.11 13.26L21 8M5 19H19C19.5304 19 20.0391 18.7893 20.4142 18.4142C20.7893 18.0391 21 17.5304 21 17V7C21 6.46957 20.7893 5.96086 20.4142 5.58579C20.0391 5.21071 19.5304 5 19 5H5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="detail-content">
                                            <span class="detail-label"><?php esc_html_e('Email', 'pexpress'); ?></span>
                                            <a href="mailto:<?php echo esc_attr($order->get_billing_email()); ?>" class="detail-value detail-link"><?php echo esc_html($order->get_billing_email()); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($meeting_type || $meeting_location) : ?>
                                    <div class="order-detail-row">
                                        <?php if ($meeting_type) : ?>
                                            <div class="order-detail-item">
                                                <div class="detail-content">
                                                    <span class="detail-label"><?php esc_html_e('Meeting Type', 'pexpress'); ?></span>
                                                    <span class="detail-value"><?php echo esc_html($meeting_type === 'meet_point' ? __('Meet Point', 'pexpress') : ($meeting_type === 'delivery_location' ? __('Delivery Location', 'pexpress') : $meeting_type)); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($meeting_location) : ?>
                                            <div class="order-detail-item order-detail-full">
                                                <div class="detail-content">
                                                    <span class="detail-label"><?php esc_html_e('Meeting Location', 'pexpress'); ?></span>
                                                    <span class="detail-value"><?php echo esc_html($meeting_location); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($delivery_id || $fridge_id || $distributor_id) : ?>
                                    <div class="order-detail-row order-detail-note">
                                        <div class="order-detail-item order-detail-full">
                                            <span class="detail-label"><?php esc_html_e('Assigned to', 'pexpress'); ?></span>
                                            <span class="detail-value">
                                                <?php
                                                $assignees = array();
                                                if ($delivery_id && ($u = get_userdata($delivery_id))) {
                                                    $assignees[] = sprintf(__('SR: %s', 'pexpress'), $u->display_name);
                                                }
                                                if ($fridge_id && ($u = get_userdata($fridge_id))) {
                                                    $assignees[] = sprintf(__('Fridge: %s', 'pexpress'), $u->display_name);
                                                }
                                                if ($distributor_id && ($u = get_userdata($distributor_id))) {
                                                    $assignees[] = sprintf(__('Product: %s', 'pexpress'), $u->display_name);
                                                }
                                                echo esc_html(implode(' · ', $assignees));
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="order-actions" style="margin-top: 10px;">
                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" class="polar-btn polar-btn-secondary" target="_blank"><?php esc_html_e('View Order', 'pexpress'); ?></a>
                                <button type="button" style="background-color: var(--polar-primary); color: #fff; border-radius: var(--polar-radius-md);" class="polar-btn polar-btn-secondary polar-view-tracking-btn" data-order-id="<?php echo esc_attr($order_id); ?>"><?php esc_html_e('View Tracking', 'pexpress'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="polar-empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3><?php esc_html_e('No completed orders', 'pexpress'); ?></h3>
                    <p><?php esc_html_e('No completed orders to show. Cancelled orders are not listed here.', 'pexpress'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<div id="polar-tracking-modal" class="polar-modal polar-tracking-modal" aria-hidden="true">
    <div class="polar-modal-overlay"></div>
    <div class="polar-modal-content polar-tracking-modal-content">
        <button type="button" class="polar-modal-close polar-tracking-modal-close" aria-label="<?php esc_attr_e('Close', 'pexpress'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18M6 6l12 12" />
            </svg>
        </button>
        <header class="polar-tracking-modal-header">
            <div class="polar-tracking-modal-title-wrap">
                <span class="polar-tracking-modal-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 6v6l4 2" />
                    </svg>
                </span>
                <div>
                    <h2 class="polar-tracking-modal-title"><?php esc_html_e('Order tracking', 'pexpress'); ?></h2>
                    <p class="polar-tracking-modal-order-id">#<span id="polar-tracking-order-id"></span></p>
                </div>
            </div>
        </header>
        <div id="polar-tracking-modal-body" class="polar-tracking-modal-body"></div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Show/hide meeting location field based on meeting type
        $('.polar-assign-form').each(function() {
            var $form = $(this);
            var $meetingType = $form.find('select[name="meeting_type"]');
            var $meetingLocationField = $form.find('.polar-meeting-location-field');

            function toggleMeetingLocation() {
                if ($meetingType.val() === 'delivery_location') {
                    $meetingLocationField.slideUp();
                } else {
                    $meetingLocationField.slideDown();
                }
            }

            // Initial state
            toggleMeetingLocation();

            // On change
            $meetingType.on('change', toggleMeetingLocation);
        });

        // History modal functionality
        var $modal = $('#polar-history-modal');
        var $modalBody = $('#polar-history-modal-body');
        var $modalOverlay = $modal.find('.polar-modal-overlay');
        var $modalClose = $modal.find('.polar-modal-close');

        function openHistoryModal(orderId, historyData, statusLabels) {
            var html = '<div class="polar-history-details-content">';
            html += '<h4>Order #' + orderId + ' - ' + '<?php esc_html_e('Status History', 'pexpress'); ?>' + '</h4>';

            if (Object.keys(historyData).length === 0) {
                html += '<p><?php esc_html_e('No history available for this order.', 'pexpress'); ?></p>';
            } else {
                for (var roleKey in historyData) {
                    if (historyData.hasOwnProperty(roleKey)) {
                        var roleData = historyData[roleKey];
                        html += '<div class="polar-history-section">';
                        html += '<strong>' + roleData.label + ':</strong>';
                        html += '<ul class="polar-history-list">';

                        roleData.entries.forEach(function(entry) {
                            var statusLabel = (statusLabels[roleKey] && statusLabels[roleKey][entry.status]) ? statusLabels[roleKey][entry.status] : entry.status;
                            html += '<li class="polar-history-entry">';
                            html += '<span class="status-badge">' + statusLabel + '</span>';
                            if (entry.note) {
                                html += '<span class="history-note">' + entry.note + '</span>';
                            }
                            html += '<span class="history-meta">' + entry.user_name + ' - ' + entry.timestamp + '</span>';
                            html += '</li>';
                        });

                        html += '</ul>';
                        html += '</div>';
                    }
                }
            }

            html += '</div>';
            $modalBody.html(html);
            $modal.fadeIn(300);
        }

        function closeHistoryModal() {
            $modal.fadeOut(300);
            $modalBody.html('');
        }

        // Open modal
        $(document).on('click', '.polar-view-history-btn', function() {
            var orderId = $(this).data('order-id');
            var historyData = $(this).data('history');
            var statusLabels = $(this).data('status-labels');

            // Parse JSON if it's a string
            if (typeof historyData === 'string') {
                historyData = JSON.parse(historyData);
            }
            if (typeof statusLabels === 'string') {
                statusLabels = JSON.parse(statusLabels);
            }

            openHistoryModal(orderId, historyData, statusLabels);
        });

        // Close modal
        $modalClose.on('click', closeHistoryModal);
        $modalOverlay.on('click', closeHistoryModal);

        // Close on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeHistoryModal();
            }
        });

        // Order Tracking modal
        var $trackingModal = $('#polar-tracking-modal');
        var $trackingModalBody = $('#polar-tracking-modal-body');
        var $trackingOrderIdSpan = $('#polar-tracking-order-id');
        var polarTrackingNonce = '<?php echo esc_js(wp_create_nonce('polar_order_tracking_nonce')); ?>';
        var polarAjaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';

        function openTrackingModal(orderId) {
            $trackingOrderIdSpan.text(orderId);
            $trackingModalBody.html('<div class="polar-tracking-loading"><div class="polar-tracking-loading-spinner"></div><p><?php echo esc_js(__('Loading tracking...', 'pexpress')); ?></p></div>');
            $trackingModal.addClass('polar-modal-open').attr('aria-hidden', 'false');

            function esc(s) {
                if (s == null || s === undefined) return '';
                var div = document.createElement('div');
                div.textContent = String(s);
                return div.innerHTML;
            }

            function stageIcon(label) {
                var l = (label || '').toLowerCase();
                if (l.indexOf('distribution') !== -1 || l.indexOf('agency') !== -1) return 'assign';
                if (l.indexOf('sr') !== -1 || l.indexOf('delivery') !== -1) return 'delivery';
                if (l.indexOf('fridge') !== -1 || l.indexOf('fsd') !== -1) return 'fridge';
                if (l.indexOf('product') !== -1 || l.indexOf('provider') !== -1 || l.indexOf('distributor') !== -1) return 'truck';
                return 'dot';
            }

            $.post(polarAjaxUrl, {
                action: 'polar_get_order_tracking',
                nonce: polarTrackingNonce,
                order_id: orderId
            }).done(function(response) {
                if (response.success && response.data) {
                    var data = response.data;
                    var html = '';
                    if (data.stage_wise && data.stage_wise.length > 0) {
                        html = '<div class="polar-tracking-timeline">';
                        data.stage_wise.forEach(function(row, idx) {
                            var statusClass = (row.current_status === 'pending') ? 'pending' : ((row.current_status === 'customer_served' || row.current_status === 'fridge_returned' || row.current_status === 'handoff_complete' || row.current_status === 'assigned' || row.current_status === 'proceeded' || row.current_status === 'completed') ? 'completed' : 'in-progress');
                            var icon = stageIcon(row.stage_label);
                            html += '<div class="polar-tracking-timeline-item polar-status-' + statusClass + '" data-stage-icon="' + esc(icon) + '">';
                            html += '<div class="polar-tracking-node">';
                            html += '<span class="polar-tracking-node-icon"></span>';
                            if (idx < data.stage_wise.length - 1) html += '<span class="polar-tracking-node-line"></span>';
                            html += '</div>';
                            html += '<div class="polar-tracking-card">';
                            html += '<div class="polar-tracking-card-header">';
                            html += '<span class="polar-tracking-card-stage">' + esc(row.stage_label) + '</span>';
                            html += '<span class="polar-tracking-pill polar-pill-' + statusClass + '">' + esc(row.status_label) + '</span>';
                            html += '</div>';
                            html += '<div class="polar-tracking-card-meta">';
                            if (row.responsible_name || row.contact_phone) {
                                if (row.responsible_name) html += '<span class="polar-tracking-person">' + esc(row.responsible_name) + '</span>';
                                if (row.contact_phone) html += ' <a href="tel:' + esc(String(row.contact_phone).replace(/[^0-9+]/g, '')) + '" class="polar-tracking-phone">' + esc(row.contact_phone) + '</a>';
                            }
                            html += '</div>';
                            if (row.last_update_formatted || row.last_update_timestamp || row.last_update_note) {
                                html += '<div class="polar-tracking-card-note">';
                                if (row.last_update_formatted || row.last_update_timestamp) {
                                    html += '<span class="polar-tracking-time">' + esc(row.last_update_formatted || row.last_update_timestamp) + '</span>';
                                    if (row.last_update_note) html += ' — ';
                                }
                                if (row.last_update_note) html += '<span class="polar-tracking-note">' + esc(row.last_update_note) + '</span>';
                                html += '</div>';
                            }
                            html += '</div></div>';
                        });
                        html += '</div>';
                    } else if (data.statuses) {
                        var s = data.statuses;
                        var stageLabels = {
                            hr: '<?php echo esc_js(__('Distribution', 'pexpress')); ?>',
                            delivery: '<?php echo esc_js(__('SR', 'pexpress')); ?>',
                            fridge: '<?php echo esc_js(__('Fridge Dept (FSD)', 'pexpress')); ?>',
                            distributor: '<?php echo esc_js(__('Product Provider', 'pexpress')); ?>'
                        };
                        var order = ['hr', 'delivery', 'fridge', 'distributor'];
                        var stageIcons = {
                            hr: 'assign',
                            delivery: 'delivery',
                            fridge: 'fridge',
                            distributor: 'truck'
                        };
                        html = '<div class="polar-tracking-timeline">';
                        order.forEach(function(key, idx) {
                            if (!s[key]) return;
                            var row = s[key];
                            var stageLabel = stageLabels[key] || key;
                            var statusClass = row.class || 'pending';
                            html += '<div class="polar-tracking-timeline-item polar-status-' + statusClass + '" data-stage-icon="' + (stageIcons[key] || 'dot') + '">';
                            html += '<div class="polar-tracking-node"><span class="polar-tracking-node-icon"></span>';
                            if (idx < order.length - 1) html += '<span class="polar-tracking-node-line"></span>';
                            html += '</div>';
                            html += '<div class="polar-tracking-card">';
                            html += '<div class="polar-tracking-card-header">';
                            html += '<span class="polar-tracking-card-stage">' + esc(stageLabel) + '</span>';
                            html += '<span class="polar-tracking-pill polar-pill-' + statusClass + '">' + esc(row.label || row.status) + '</span>';
                            html += '</div>';
                            if (row.user_name) html += '<div class="polar-tracking-card-meta"><span class="polar-tracking-person">' + esc(row.user_name) + '</span></div>';
                            html += '</div></div>';
                        });
                        html += '</div>';
                    }
                    if (html) {
                        $trackingModalBody.html(html);
                    } else {
                        $trackingModalBody.html('<p class="polar-tracking-error"><?php echo esc_js(__('Unable to load tracking.', 'pexpress')); ?></p>');
                    }
                } else {
                    $trackingModalBody.html('<p class="polar-tracking-error"><?php echo esc_js(__('Unable to load tracking.', 'pexpress')); ?></p>');
                }
            }).fail(function() {
                $trackingModalBody.html('<p class="polar-tracking-error"><?php echo esc_js(__('Unable to load tracking.', 'pexpress')); ?></p>');
            });
        }

        function closeTrackingModal() {
            $trackingModal.removeClass('polar-modal-open').attr('aria-hidden', 'true');
        }

        $(document).on('click', '.polar-view-tracking-btn', function() {
            var orderId = $(this).data('order-id');
            if (orderId) {
                openTrackingModal(orderId);
            }
        });

        $trackingModal.find('.polar-modal-close, .polar-modal-overlay').on('click', closeTrackingModal);
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $trackingModal.is(':visible')) {
                closeTrackingModal();
            }
        });
    });
</script>