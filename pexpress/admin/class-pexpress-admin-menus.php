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
        $main_page_callback = 'render_agency_dashboard';
        if (in_array('polar_delivery', $current_user->roles)) {
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

        // HR Dashboard (formerly Delivery) - Only show for HR users, not for Agency users
        if (in_array('polar_delivery', $current_user->roles) || (current_user_can('manage_woocommerce') && !in_array('polar_hr', $current_user->roles))) {
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
        <div class="wrap">
            <h1><?php echo esc_html__('Email Log', 'pexpress'); ?></h1>

            <div class="pexpress-email-log-filters" style="margin: 20px 0;">
                <form method="get" action="">
                    <input type="hidden" name="page" value="polar-express-email-log">
                    <select name="status">
                        <option value=""><?php echo esc_html__('All Statuses', 'pexpress'); ?></option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php echo esc_html__('Pending', 'pexpress'); ?></option>
                        <option value="success" <?php selected($status_filter, 'success'); ?>><?php echo esc_html__('Success', 'pexpress'); ?></option>
                        <option value="failed" <?php selected($status_filter, 'failed'); ?>><?php echo esc_html__('Failed', 'pexpress'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php echo esc_attr__('Filter', 'pexpress'); ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-email-log&action=clear_all&_wpnonce=' . wp_create_nonce('pexpress_email_log_action'))); ?>" 
                       class="button" 
                       onclick="return confirm('<?php echo esc_js(__('Are you sure you want to clear all logs?', 'pexpress')); ?>');">
                        <?php echo esc_html__('Clear All Logs', 'pexpress'); ?>
                    </a>
                </form>
            </div>

            <div class="pexpress-email-log-stats" style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccd0d4;">
                <strong><?php echo esc_html__('Total Logs:', 'pexpress'); ?></strong> <?php echo number_format($total_logs); ?> |
                <strong><?php echo esc_html__('Success:', 'pexpress'); ?></strong> <?php echo number_format(PExpress_Email_Log::get_log_count('success')); ?> |
                <strong><?php echo esc_html__('Failed:', 'pexpress'); ?></strong> <?php echo number_format(PExpress_Email_Log::get_log_count('failed')); ?> |
                <strong><?php echo esc_html__('Pending:', 'pexpress'); ?></strong> <?php echo number_format(PExpress_Email_Log::get_log_count('pending')); ?>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 80px;"><?php echo esc_html__('ID', 'pexpress'); ?></th>
                        <th><?php echo esc_html__('To', 'pexpress'); ?></th>
                        <th><?php echo esc_html__('Subject', 'pexpress'); ?></th>
                        <th style="width: 120px;"><?php echo esc_html__('Method', 'pexpress'); ?></th>
                        <th style="width: 100px;"><?php echo esc_html__('Status', 'pexpress'); ?></th>
                        <th style="width: 120px;"><?php echo esc_html__('Response Code', 'pexpress'); ?></th>
                        <th style="width: 150px;"><?php echo esc_html__('Date', 'pexpress'); ?></th>
                        <th style="width: 100px;"><?php echo esc_html__('Actions', 'pexpress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                <?php echo esc_html__('No email logs found.', 'pexpress'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($logs as $log) : ?>
                            <tr>
                                <td><?php echo esc_html($log['id']); ?></td>
                                <td>
                                    <strong><?php echo esc_html($log['to_email']); ?></strong>
                                </td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo esc_attr($log['subject']); ?>">
                                        <?php echo esc_html($log['subject']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="dashicons dashicons-<?php echo $log['method'] === 'mailgun' ? 'email-alt' : 'email'; ?>" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                    <?php echo esc_html(ucfirst($log['method'])); ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = 'pending';
                                    $status_label = __('Pending', 'pexpress');
                                    if ($log['status'] === 'success') {
                                        $status_class = 'success';
                                        $status_label = __('Success', 'pexpress');
                                    } elseif ($log['status'] === 'failed') {
                                        $status_class = 'error';
                                        $status_label = __('Failed', 'pexpress');
                                    }
                                    ?>
                                    <span class="status-<?php echo esc_attr($status_class); ?>" style="padding: 3px 8px; border-radius: 3px; background: <?php echo $status_class === 'success' ? '#46b450' : ($status_class === 'error' ? '#dc3232' : '#ffb900'); ?>; color: #fff; font-size: 11px;">
                                        <?php echo esc_html($status_label); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($log['response_code'])) : ?>
                                        <code><?php echo esc_html($log['response_code']); ?></code>
                                    <?php else : ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at']))); ?>
                                </td>
                                <td>
                                    <a href="#" class="button button-small pexpress-view-log-details" data-log-id="<?php echo esc_attr($log['id']); ?>">
                                        <?php echo esc_html__('View', 'pexpress'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=polar-express-email-log&action=delete&log_id=' . $log['id']), 'pexpress_email_log_action')); ?>" 
                                       class="button button-small" 
                                       onclick="return confirm('<?php echo esc_js(__('Delete this log entry?', 'pexpress')); ?>');">
                                        <?php echo esc_html__('Delete', 'pexpress'); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr class="pexpress-log-details" id="log-details-<?php echo esc_attr($log['id']); ?>" style="display: none;">
                                <td colspan="8" style="background: #f9f9f9; padding: 20px;">
                                    <h4><?php echo esc_html__('Log Details', 'pexpress'); ?></h4>
                                    <table style="width: 100%;">
                                        <tr>
                                            <th style="text-align: left; width: 150px;"><?php echo esc_html__('Error Message:', 'pexpress'); ?></th>
                                            <td><?php echo !empty($log['error_message']) ? esc_html($log['error_message']) : '<span style="color: #999;">-</span>'; ?></td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: left;"><?php echo esc_html__('Response Body:', 'pexpress'); ?></th>
                                            <td>
                                                <?php if (!empty($log['response_body'])) : ?>
                                                    <pre style="background: #fff; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow: auto; font-size: 12px;"><?php echo esc_html($log['response_body']); ?></pre>
                                                <?php else : ?>
                                                    <span style="color: #999;">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: left;"><?php echo esc_html__('Message Preview:', 'pexpress'); ?></th>
                                            <td>
                                                <div style="max-height: 200px; overflow: auto; background: #fff; padding: 10px; border: 1px solid #ddd;">
                                                    <?php echo wp_kses_post(substr($log['message'], 0, 500)); ?>
                                                    <?php if (strlen($log['message']) > 500) : ?>
                                                        <em>... (truncated)</em>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: left;"><?php echo esc_html__('Headers:', 'pexpress'); ?></th>
                                            <td>
                                                <?php
                                                $headers = maybe_unserialize($log['headers']);
                                                if (is_array($headers)) {
                                                    echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; max-height: 150px; overflow: auto; font-size: 12px;">';
                                                    print_r($headers);
                                                    echo '</pre>';
                                                } else {
                                                    echo esc_html($log['headers']);
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

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

        <script>
        jQuery(document).ready(function($) {
            $('.pexpress-view-log-details').on('click', function(e) {
                e.preventDefault();
                var logId = $(this).data('log-id');
                $('#log-details-' + logId).toggle();
            });
        });
        </script>
        <?php
    }
}
