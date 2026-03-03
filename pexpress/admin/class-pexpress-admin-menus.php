<?php

/**
 * Admin menu registration
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin menus handler
 */
class PExpress_Admin_Menus
{

    /**
     * Register admin menus
     */
    public function register_menus()
    {
        // Check if user has any Polar Express role or manage_woocommerce
        $current_user = wp_get_current_user();
        $has_role = in_array('polar_hr', $current_user->roles) ||
            in_array('polar_delivery', $current_user->roles) ||
            in_array('polar_fridge', $current_user->roles) ||
            in_array('polar_distributor', $current_user->roles) ||
            in_array('polar_support', $current_user->roles) ||
            current_user_can('manage_woocommerce');

        // Always register the order edit page (hidden menu) so it's accessible
        // even if capabilities are granted via filters
        add_submenu_page(
            null, // Hidden from menu
            __('Edit Order', 'pexpress'),
            __('Edit Order', 'pexpress'),
            'read', // Use basic read capability, actual permissions checked in render method
            'polar-express-order-edit',
            array($this, 'render_order_edit_page')
        );

        if (!$has_role) {
            return;
        }

        // Main menu - visible to all Polar Express roles
        $main_capability = 'read'; // Basic read capability

        // Determine which dashboard to show based on role
        // Priority: Agency (polar_hr) always takes precedence, even if user has other roles
        $main_page_callback = 'render_agency_dashboard';
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            // Agency Dashboard - highest priority
            $main_page_callback = 'render_agency_dashboard';
        } elseif (in_array('polar_delivery', $current_user->roles)) {
            $main_page_callback = 'render_hr_dashboard';
        } elseif (in_array('polar_fridge', $current_user->roles)) {
            $main_page_callback = 'render_fridge_dashboard';
        } elseif (in_array('polar_distributor', $current_user->roles)) {
            $main_page_callback = 'render_distributor_dashboard';
        } elseif (in_array('polar_support', $current_user->roles)) {
            $main_page_callback = 'render_support_dashboard';
        }

        // Set the main menu title and label based on role
        $main_menu_title = __('Polar Express', 'pexpress');
        $main_menu_label = __('Polar Express', 'pexpress');

        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            $main_menu_title = __('Agency Dashboard', 'pexpress');
            $main_menu_label = __('Agency Dashboard', 'pexpress');
        } elseif (in_array('polar_delivery', $current_user->roles)) {
            $main_menu_title = __('HR Dashboard', 'pexpress');
            $main_menu_label = __('HR Dashboard', 'pexpress');
        } elseif (in_array('polar_support', $current_user->roles)) {
            $main_menu_title = __('Support Portal', 'pexpress');
            $main_menu_label = __('Support Portal', 'pexpress');
        }

        add_menu_page(
            $main_menu_title,
            __('Polar Express', 'pexpress'),
            $main_capability,
            'polar-express',
            array($this, $main_page_callback),
            'dashicons-clipboard',
            56
        );

        // Add explicit submenu for Agency Dashboard so it appears in the submenu list
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Agency Dashboard', 'pexpress'),
                __('Agency Dashboard', 'pexpress'),
                'read',
                'polar-express',
                array($this, 'render_agency_dashboard')
            );
        }

        // HR Dashboard (formerly Delivery) - Show for delivery users
        // Users with polar_hr can also access this if they have polar_delivery role
        if (in_array('polar_delivery', $current_user->roles)) {
            add_submenu_page(
                'polar-express',
                __('HR Dashboard', 'pexpress'),
                __('HR Dashboard', 'pexpress'),
                'read',
                'polar-express-delivery',
                array($this, 'render_hr_dashboard')
            );
        }

        // Fridge Dashboard
        if (in_array('polar_fridge', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Fridge Dashboard', 'pexpress'),
                __('Fridge Dashboard', 'pexpress'),
                'read',
                'polar-express-fridge',
                array($this, 'render_fridge_dashboard')
            );
        }

        // Distributor Dashboard
        if (in_array('polar_distributor', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Distributor Fulfills', 'pexpress'),
                __('Distributor Fulfills', 'pexpress'),
                'read',
                'polar-express-distributor',
                array($this, 'render_distributor_dashboard')
            );
        }

        // Support Dashboard
        if (in_array('polar_support', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Support Portal', 'pexpress'),
                __('Support Portal', 'pexpress'),
                'read',
                'polar-express-support',
                array($this, 'render_support_dashboard')
            );
        }


        // Settings (HR and Shop Managers only)
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Configuration', 'pexpress'),
                __('Configuration', 'pexpress'),
                'manage_woocommerce',
                'polar-express-settings',
                array($this, 'render_settings_page')
            );
        }

        // Email Log (HR and Shop Managers only)
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Email Log', 'pexpress'),
                __('Email Log', 'pexpress'),
                'manage_woocommerce',
                'polar-express-email-log',
                array($this, 'render_email_log_page')
            );
        }

        // Test Mail (HR and Shop Managers only)
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Test Mail', 'pexpress'),
                __('Test Mail', 'pexpress'),
                'manage_woocommerce',
                'polar-express-test-mail',
                array($this, 'render_test_mail_page')
            );
        }

        // Setup Wizard (HR and Shop Managers only) - Show if setup not completed
        if ((in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) && !PExpress_Admin_Setup_Wizard::is_setup_completed()) {
            add_submenu_page(
                'polar-express',
                __('Setup Wizard', 'pexpress'),
                __('Setup Wizard', 'pexpress'),
                'manage_woocommerce',
                'polar-express-setup-wizard',
                array($this, 'render_setup_wizard_page')
            );
        }

        // Setup Guideline (HR and Shop Managers only)
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Setup Guideline', 'pexpress'),
                __('Setup Guideline', 'pexpress'),
                'manage_woocommerce',
                'polar-express-setup',
                array($this, 'render_setup_guideline_page')
            );
        }

        // Changelog (HR and Shop Managers only)
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Changelog', 'pexpress'),
                __('Changelog', 'pexpress'),
                'manage_woocommerce',
                'polar-express-changelog',
                array($this, 'render_changelog_page')
            );
        }

        // Role Capabilities (HR and Shop Managers only)
        if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'polar-express',
                __('Role Capabilities', 'pexpress'),
                __('Role Capabilities', 'pexpress'),
                'manage_woocommerce',
                'polar-express-role-capabilities',
                array($this, 'render_role_capabilities_page')
            );
        }
    }

    /**
     * Output a small script that rewrites sidebar links in the browser only.
     * Fixes "handai" prefix without touching PHP menu globals (avoids 404 / missing submenus).
     */
    public function fix_menu_links_js()
    {
        ?>
        <script>
        (function(){
            document.querySelectorAll('#adminmenu a[href*="page=handaipolar-express"]').forEach(function(a){
                a.href = a.href.replace(/page=handaipolar-express/g, 'page=polar-express');
            });
        })();
        </script>
        <?php
    }

    /**
     * Redirect wrong page= URLs (e.g. handaipolar-express-fridge) to the correct slug.
     * Fixes 404s when another plugin or environment corrupts the menu link.
     */
    public function redirect_wrong_page_slugs()
    {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if ($page === '' || strpos($page, 'polar-express') === false) {
            return;
        }
        $prefix = 'handai';
        if (strpos($page, $prefix) !== 0) {
            return;
        }
        $correct = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $page);
        if ($correct === '' || $correct === $page) {
            return;
        }
        wp_safe_redirect(admin_url('admin.php?page=' . $correct));
        exit;
    }

    /**
     * Render Agency Dashboard page
     */
    public function render_agency_dashboard()
    {
        $dashboards = new PExpress_Admin_Dashboards();
        $dashboards->render_agency_dashboard();
    }

    /**
     * Render HR Dashboard page
     */
    public function render_hr_dashboard()
    {
        $dashboards = new PExpress_Admin_Dashboards();
        $dashboards->render_hr_dashboard();
    }

    /**
     * Render Fridge Dashboard page
     */
    public function render_fridge_dashboard()
    {
        $dashboards = new PExpress_Admin_Dashboards();
        $dashboards->render_fridge_dashboard();
    }

    /**
     * Render Distributor Dashboard page
     */
    public function render_distributor_dashboard()
    {
        $dashboards = new PExpress_Admin_Dashboards();
        $dashboards->render_distributor_dashboard();
    }

    /**
     * Render Support Dashboard page
     */
    public function render_support_dashboard()
    {
        $dashboards = new PExpress_Admin_Dashboards();
        $dashboards->render_support_dashboard();
    }

    /**
     * Render Settings page
     */
    public function render_settings_page()
    {
        $settings = new PExpress_Admin_Settings();
        $settings->render_settings_page();
    }

    /**
     * Render Setup Guideline page
     */
    public function render_setup_guideline_page()
    {
        $pages = new PExpress_Admin_Pages();
        $pages->render_setup_guideline_page();
    }

    /**
     * Render Changelog page
     */
    public function render_changelog_page()
    {
        $pages = new PExpress_Admin_Pages();
        $pages->render_changelog_page();
    }

    /**
     * Render Order Edit page
     */
    public function render_order_edit_page()
    {
        $order_manipulation = new PExpress_Admin_Order_Manipulation();
        $order_manipulation->render_order_edit_page();
    }

    /**
     * Render Setup Wizard page
     */
    public function render_setup_wizard_page()
    {
        $wizard = new PExpress_Admin_Setup_Wizard();
        $wizard->render_setup_wizard();
    }

    /**
     * Render Role Capabilities page
     */
    public function render_role_capabilities_page()
    {
        $capabilities = new PExpress_Role_Capabilities();
        $capabilities->render_role_capabilities_page();
    }

    /**
     * Render Test Mail page
     */
    public function render_test_mail_page()
    {
        $pages = new PExpress_Admin_Pages();
        $pages->render_test_mail_page();
    }

    /**
     * Render Email Log page
     */
    public function render_email_log_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        // Handle actions
        if (isset($_GET['action']) && isset($_GET['log_id']) && wp_verify_nonce($_GET['_wpnonce'], 'pexpress_email_log_action')) {
            $log_id = absint($_GET['log_id']);
            $action = sanitize_text_field($_GET['action']);

            if ($action === 'delete' && class_exists('PExpress_Email_Log')) {
                PExpress_Email_Log::delete_log($log_id);
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Log entry deleted.', 'pexpress') . '</p></div>';
            } elseif ($action === 'clear_all' && class_exists('PExpress_Email_Log')) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'pexpress_email_log';
                $wpdb->query("TRUNCATE TABLE $table_name");
                echo '<div class="notice notice-success is-dismissible"><p>' . __('All logs cleared.', 'pexpress') . '</p></div>';
            }
        }

        // Get filter
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 50;
        $offset = ($current_page - 1) * $per_page;

        // Get logs
        $logs = array();
        $total_logs = 0;
        if (class_exists('PExpress_Email_Log')) {
            $logs = PExpress_Email_Log::get_logs($per_page, $offset, $status_filter);
            $total_logs = PExpress_Email_Log::get_log_count($status_filter);
        }

        $total_pages = ceil($total_logs / $per_page);

?>
        <div class="wrap pexpress-email-log-wrap">
            <div style="background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); padding: 30px; margin: -20px -20px 20px -20px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                    <span class="dashicons dashicons-email-alt" style="font-size: 32px; width: 32px; height: 32px;"></span>
                    <?php echo esc_html__('Email Log', 'pexpress'); ?>
                </h1>
                <p style="color: #ffffff; margin: 10px 0 0 0; opacity: 0.95; font-size: 14px;"><?php echo esc_html__('Monitor and track all email notifications sent through the system', 'pexpress'); ?></p>
            </div>

            <div class="pexpress-email-log-filters" style="background: #fff; padding: 20px; margin: 0 0 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 6px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <form method="get" action="" style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 300px;">
                    <input type="hidden" name="page" value="polar-express-email-log">
                    <label style="font-weight: 500; color: #1d2327; margin-right: 5px;"><?php echo esc_html__('Filter:', 'pexpress'); ?></label>
                    <select name="status" style="padding: 6px 12px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 14px; min-width: 150px;">
                        <option value=""><?php echo esc_html__('All Statuses', 'pexpress'); ?></option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php echo esc_html__('Pending', 'pexpress'); ?></option>
                        <option value="success" <?php selected($status_filter, 'success'); ?>><?php echo esc_html__('Success', 'pexpress'); ?></option>
                        <option value="failed" <?php selected($status_filter, 'failed'); ?>><?php echo esc_html__('Failed', 'pexpress'); ?></option>
                    </select>
                    <input type="submit" class="button button-primary" value="<?php echo esc_attr__('Apply Filter', 'pexpress'); ?>" style="margin: 0;">
                </form>
                <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-email-log&action=clear_all&_wpnonce=' . wp_create_nonce('pexpress_email_log_action'))); ?>"
                    class="button"
                    style="background: #dc3232; border-color: #dc3232; color: #fff; margin: 0;"
                    onclick="return confirm('<?php echo esc_js(__('Are you sure you want to clear all logs?', 'pexpress')); ?>');">
                    <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;"></span>
                    <?php echo esc_html__('Clear All Logs', 'pexpress'); ?>
                </a>
            </div>

            <div class="pexpress-email-log-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 0 0 20px 0;">
                <div style="background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #fff;">
                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Total Logs', 'pexpress'); ?></div>
                    <div style="font-size: 32px; font-weight: 700; line-height: 1;"><?php echo number_format($total_logs); ?></div>
                </div>
                <div style="background: linear-gradient(135deg, #00a32a 0%, #007a20 100%); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #fff;">
                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Success', 'pexpress'); ?></div>
                    <div style="font-size: 32px; font-weight: 700; line-height: 1;"><?php echo number_format(PExpress_Email_Log::get_log_count('success')); ?></div>
                </div>
                <div style="background: linear-gradient(135deg, #dc3232 0%, #b32d2e 100%); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #fff;">
                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Failed', 'pexpress'); ?></div>
                    <div style="font-size: 32px; font-weight: 700; line-height: 1;"><?php echo number_format(PExpress_Email_Log::get_log_count('failed')); ?></div>
                </div>
                <div style="background: linear-gradient(135deg, #ffb900 0%, #dba617 100%); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #fff;">
                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Pending', 'pexpress'); ?></div>
                    <div style="font-size: 32px; font-weight: 700; line-height: 1;"><?php echo number_format(PExpress_Email_Log::get_log_count('pending')); ?></div>
                </div>
            </div>

            <div style="background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 6px; overflow: hidden;">
                <table class="wp-list-table widefat fixed striped" style="margin: 0; border: none;">
                    <thead>
                        <tr style="background: #f6f7f7;">
                            <th style="width: 80px; padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1;"><?php echo esc_html__('ID', 'pexpress'); ?></th>
                            <th style="padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1;"><?php echo esc_html__('To', 'pexpress'); ?></th>
                            <th style="padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1;"><?php echo esc_html__('Subject', 'pexpress'); ?></th>
                            <th style="width: 120px; padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1;"><?php echo esc_html__('Method', 'pexpress'); ?></th>
                            <th style="width: 120px; padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1;"><?php echo esc_html__('Status', 'pexpress'); ?></th>
                            <th style="width: 120px; padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1;"><?php echo esc_html__('Response Code', 'pexpress'); ?></th>
                            <th style="width: 150px; padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1;"><?php echo esc_html__('Date', 'pexpress'); ?></th>
                            <th style="width: 140px; padding: 15px; font-weight: 600; color: #1d2327; border-bottom: 2px solid #2271b1; text-align: center;"><?php echo esc_html__('Actions', 'pexpress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)) : ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 60px 20px;">
                                    <div style="color: #646970;">
                                        <span class="dashicons dashicons-email-alt" style="font-size: 64px; width: 64px; height: 64px; display: block; margin: 0 auto 20px; opacity: 0.3;"></span>
                                        <p style="font-size: 16px; margin: 0; font-weight: 500;"><?php echo esc_html__('No email logs found.', 'pexpress'); ?></p>
                                        <p style="font-size: 14px; margin: 10px 0 0 0; color: #8c8f94;"><?php echo esc_html__('Email logs will appear here once emails are sent.', 'pexpress'); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($logs as $log) : ?>
                                <tr style="border-bottom: 1px solid #f0f0f1;">
                                    <td style="padding: 15px; color: #646970; font-weight: 600;"><?php echo esc_html($log['id']); ?></td>
                                    <td style="padding: 15px;">
                                        <strong style="color: #1d2327; font-size: 14px;"><?php echo esc_html($log['to_email']); ?></strong>
                                    </td>
                                    <td style="padding: 15px;">
                                        <div style="max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1d2327;" title="<?php echo esc_attr($log['subject']); ?>">
                                            <?php echo esc_html($log['subject']); ?>
                                        </div>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="display: inline-flex; align-items: center; gap: 6px; color: #646970;">
                                            <span class="dashicons dashicons-<?php echo $log['method'] === 'mailgun' ? 'email-alt' : 'email'; ?>" style="font-size: 16px; width: 16px; height: 16px; color: #2271b1;"></span>
                                            <span style="font-size: 13px;"><?php echo esc_html(ucfirst($log['method'])); ?></span>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php
                                        $status_class = 'pending';
                                        $status_label = __('Pending', 'pexpress');
                                        $status_color = '#ffb900';
                                        if ($log['status'] === 'success') {
                                            $status_class = 'success';
                                            $status_label = __('Success', 'pexpress');
                                            $status_color = '#00a32a';
                                        } elseif ($log['status'] === 'failed') {
                                            $status_class = 'error';
                                            $status_label = __('Failed', 'pexpress');
                                            $status_color = '#dc3232';
                                        }
                                        ?>
                                        <span class="status-<?php echo esc_attr($status_class); ?>" style="display: inline-block; padding: 4px 10px; border-radius: 12px; background: <?php echo esc_attr($status_color); ?>; color: #fff; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <?php echo esc_html($status_label); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php if (!empty($log['response_code'])) : ?>
                                            <code style="background: #f6f7f7; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #2271b1; border: 1px solid #dcdcde;"><?php echo esc_html($log['response_code']); ?></code>
                                        <?php else : ?>
                                            <span style="color: #8c8f94; font-size: 13px;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 15px; color: #646970; font-size: 13px;">
                                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at']))); ?>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                            <a href="#" class="button button-small pexpress-view-log-details" data-log-id="<?php echo esc_attr($log['id']); ?>" style="padding: 4px 10px; height: auto; line-height: 1.5;">
                                                <span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                            </a>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=polar-express-email-log&action=delete&log_id=' . $log['id']), 'pexpress_email_log_action')); ?>"
                                                class="button button-small"
                                                style="padding: 4px 10px; height: auto; line-height: 1.5; color: #dc3232; border-color: #dc3232;"
                                                onclick="return confirm('<?php echo esc_js(__('Delete this log entry?', 'pexpress')); ?>');">
                                                <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="pexpress-log-details" id="log-details-<?php echo esc_attr($log['id']); ?>" style="display: none;">
                                    <td colspan="8" style="background: #f6f7f7; padding: 25px; border-top: 2px solid #2271b1;">
                                        <div style="background: #fff; padding: 20px; border-radius: 6px; border: 1px solid #dcdcde;">
                                            <h4 style="margin: 0 0 20px 0; color: #1d2327; font-size: 18px; font-weight: 600; padding-bottom: 10px; border-bottom: 2px solid #f0f0f1;">
                                                <span class="dashicons dashicons-info" style="font-size: 18px; width: 18px; height: 18px; vertical-align: middle; margin-right: 8px; color: #2271b1;"></span>
                                                <?php echo esc_html__('Log Details', 'pexpress'); ?>
                                            </h4>
                                            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                                                <div>
                                                    <strong style="display: block; color: #1d2327; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Error Message', 'pexpress'); ?></strong>
                                                    <div style="background: #fff; padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; color: #646970; font-size: 13px;">
                                                        <?php echo !empty($log['error_message']) ? esc_html($log['error_message']) : '<span style="color: #8c8f94;">-</span>'; ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong style="display: block; color: #1d2327; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Response Body', 'pexpress'); ?></strong>
                                                    <?php if (!empty($log['response_body'])) : ?>
                                                        <pre style="background: #fff; padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; max-height: 200px; overflow: auto; font-size: 12px; line-height: 1.5; color: #1d2327;"><?php echo esc_html($log['response_body']); ?></pre>
                                                    <?php else : ?>
                                                        <div style="background: #fff; padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; color: #8c8f94; font-size: 13px;">-</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <strong style="display: block; color: #1d2327; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Message Preview', 'pexpress'); ?></strong>
                                                    <div style="max-height: 200px; overflow: auto; background: #fff; padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; font-size: 13px; line-height: 1.6;">
                                                        <?php echo wp_kses_post(substr($log['message'], 0, 500)); ?>
                                                        <?php if (strlen($log['message']) > 500) : ?>
                                                            <em style="color: #8c8f94;">... (truncated)</em>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong style="display: block; color: #1d2327; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html__('Headers', 'pexpress'); ?></strong>
                                                    <?php
                                                    $headers = maybe_unserialize($log['headers']);
                                                    if (is_array($headers)) {
                                                        echo '<pre style="background: #fff; padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; max-height: 150px; overflow: auto; font-size: 12px; line-height: 1.5; color: #1d2327;">';
                                                        print_r($headers);
                                                        echo '</pre>';
                                                    } else {
                                                        echo '<div style="background: #fff; padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; color: #646970; font-size: 13px;">' . esc_html($log['headers']) . '</div>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_pages,
                            'current' => $current_page,
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .pexpress-email-log-wrap {
                max-width: 100%;
            }

            .pexpress-email-log-wrap .button-small {
                min-width: auto;
            }

            @media (max-width: 782px) {
                .pexpress-email-log-stats {
                    grid-template-columns: 1fr !important;
                }

                .pexpress-email-log-filters {
                    flex-direction: column !important;
                    align-items: stretch !important;
                }

                .pexpress-email-log-filters form {
                    flex-direction: column !important;
                    min-width: auto !important;
                }
            }
        </style>
        <script>
            jQuery(document).ready(function($) {
                $('.pexpress-view-log-details').on('click', function(e) {
                    e.preventDefault();
                    var logId = $(this).data('log-id');
                    var $details = $('#log-details-' + logId);
                    $details.slideToggle(200);
                    $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
                });
            });
        </script>
<?php
    }
}
