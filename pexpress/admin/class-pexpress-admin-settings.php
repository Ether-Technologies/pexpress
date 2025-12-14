<?php

/**
 * Admin settings management - Main coordinator
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin settings handler - Main coordinator
 */
class PExpress_Admin_Settings
{
    /**
     * Settings modules
     *
     * @var array
     */
    private $modules = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->load_modules();
    }

    /**
     * Load settings modules
     */
    private function load_modules()
    {
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-settings-sms.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-settings-email.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-settings-templates.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-settings-general.php';

        $this->modules['sms'] = new PExpress_Admin_Settings_SMS();
        $this->modules['email'] = new PExpress_Admin_Settings_Email();
        $this->modules['templates'] = new PExpress_Admin_Settings_Templates();
        $this->modules['general'] = new PExpress_Admin_Settings_General();
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('pexpress_settings_group', 'pexpress_options', array(
            'sanitize_callback' => array($this, 'sanitize_options')
        ));

        // Register settings from all modules
        foreach ($this->modules as $module) {
            if (method_exists($module, 'register_settings')) {
                $module->register_settings();
            }
        }
    }

    /**
     * Sanitize and merge options
     */
    public function sanitize_options($input)
    {
        if (!is_array($input)) {
            return get_option('pexpress_options', array());
        }

        // Get existing options to merge with
        $existing_options = get_option('pexpress_options', array());

        // Recursively merge and sanitize
        $sanitized = $this->merge_and_sanitize($existing_options, $input);

        return $sanitized;
    }

    /**
     * Recursively merge and sanitize arrays
     */
    private function merge_and_sanitize($existing, $input)
    {
        $result = $existing;

        foreach ($input as $key => $value) {
            $key = sanitize_key($key);

            if (is_array($value)) {
                if (!isset($result[$key]) || !is_array($result[$key])) {
                    $result[$key] = array();
                }
                $result[$key] = $this->merge_and_sanitize($result[$key], $value);
            } else {
                // For checkboxes, preserve '1' or empty string
                if ($value === '1' || $value === 1) {
                    $result[$key] = '1';
                } elseif ($value === '' || $value === null) {
                    $result[$key] = '';
                } else {
                    // Sanitize text fields
                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                        $result[$key] = esc_url_raw($value);
                    } else {
                        $result[$key] = sanitize_text_field($value);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get role description
     */
    public function get_role_description($role_key)
    {
        $descriptions = array(
            'polar_hr' => __('Full access to assign orders and manage operations (Agency)', 'pexpress'),
            'polar_delivery' => __('Can view and update delivery status for assigned orders (SR)', 'pexpress'),
            'polar_fridge' => __('Can view and mark fridge collection for assigned orders', 'pexpress'),
            'polar_distributor' => __('Can view and mark fulfillment for assigned orders', 'pexpress'),
            'polar_support' => __('Can view all orders and provide customer support', 'pexpress'),
        );
        return isset($descriptions[$role_key]) ? $descriptions[$role_key] : '';
    }

    /**
     * Render Settings page
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $roles = new PExpress_Admin_Roles();

        // Polar Express roles
        $polar_roles = array(
            'polar_hr' => __('Polar Agency', 'pexpress'),
            'polar_delivery' => __('Polar SR', 'pexpress'),
            'polar_fridge' => __('Polar Fridge Provider', 'pexpress'),
            'polar_distributor' => __('Polar Product Provider', 'pexpress'),
            'polar_support' => __('Polar Support', 'pexpress'),
        );

        // Get users for each role
        $role_users = array();
        foreach ($polar_roles as $role_key => $role_name) {
            $role_users[$role_key] = get_users(array('role' => $role_key));
        }

        // Get all users for the add user form
        $all_users = get_users(array('number' => -1, 'orderby' => 'display_name'));

        // Get current tab from URL
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'roles';

        echo '<div class="wrap pexpress-settings-wrap polar-dashboard">';
        echo '<div class="polar-dashboard-header" style="background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); padding: 40px 30px; margin: -20px -20px 30px -20px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">';
        echo '<div class="polar-header-content">';
        echo '<h1 class="polar-dashboard-title" style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 700; display: flex; align-items: center; gap: 12px;">';
        echo '<span class="dashicons dashicons-admin-settings" style="font-size: 36px; width: 36px; height: 36px; color: #ffffff;"></span>';
        echo esc_html__('Polar Express Settings', 'pexpress');
        echo '</h1>';
        echo '<p class="polar-dashboard-subtitle" style="color: rgba(255,255,255,0.95); margin: 12px 0 0 0; font-size: 16px;">' . esc_html__('Configure your Polar Express plugin settings and preferences', 'pexpress') . '</p>';
        echo '</div>';
        echo '</div>';

        // Tab Navigation
        $tabs = array(
            'roles' => array(
                'label' => __('Role Management', 'pexpress'),
                'icon' => 'groups',
            ),
            'sms' => array(
                'label' => __('SMS Settings', 'pexpress'),
                'icon' => 'smartphone',
            ),
            'email' => array(
                'label' => __('Email Settings', 'pexpress'),
                'icon' => 'email-alt',
            ),
            'templates' => array(
                'label' => __('Templates', 'pexpress'),
                'icon' => 'editor-code',
            ),
            'general' => array(
                'label' => __('General', 'pexpress'),
                'icon' => 'admin-settings',
            ),
        );

        echo '<nav class="nav-tab-wrapper pexpress-nav-tabs" style="margin: 20px 0 0 0;">';
        foreach ($tabs as $tab_key => $tab_info) {
            $active = ($current_tab === $tab_key) ? 'nav-tab-active' : '';
            $url = add_query_arg(array('page' => 'polar-express-settings', 'tab' => $tab_key), admin_url('admin.php'));

            // Define accent colors for each tab
            $accent_colors = array(
                'roles' => '#2271b1',
                'sms' => '#00a32a',
                'email' => '#d63638',
                'templates' => '#ffb900',
                'general' => '#8c8f94',
            );
            $accent_color = isset($accent_colors[$tab_key]) ? $accent_colors[$tab_key] : '#2271b1';

            echo '<a href="' . esc_url($url) . '" class="nav-tab ' . esc_attr($active) . '" data-tab="' . esc_attr($tab_key) . '" style="' . ($active ? 'border-bottom-color: ' . esc_attr($accent_color) . ' !important; color: ' . esc_attr($accent_color) . ' !important;' : '') . '">';
            echo '<span class="dashicons dashicons-' . esc_attr($tab_info['icon']) . '" style="vertical-align: middle; margin-right: 8px; font-size: 18px; width: 18px; height: 18px;"></span>';
            echo esc_html($tab_info['label']);
            echo '</a>';
        }
        echo '</nav>';

        // Tab Content
        echo '<div class="pexpress-tab-content" style="margin-top: 0;">';

        // Role Management Tab
        if ($current_tab === 'roles') {
            echo '<div class="pexpress-tab-panel" id="tab-roles">';
            echo '<div class="polar-tasks-section" style="background: #fff; padding: 32px; margin: 0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">';
            echo '<div class="polar-section-header" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h2 class="polar-section-title" style="font-size: 24px; font-weight: 700; color: #1d2327; margin: 0; display: flex; align-items: center; gap: 12px;">';
            echo '<span class="dashicons dashicons-groups" style="color: #2271b1; font-size: 28px; width: 28px; height: 28px;"></span>';
            echo esc_html__('Role Management', 'pexpress');
            echo '</h2>';
            echo '<p style="color: #646970; margin: 8px 0 0 0; font-size: 14px;">' . esc_html__('Manage users assigned to each Polar Express role', 'pexpress') . '</p>';
            echo '</div>';
            echo '<div class="polar-role-management-section" style="padding: 0;">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">';
            echo '<button type="button" id="polar-add-user-btn" class="button button-primary button-large" style="background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); border: none; padding: 12px 24px; font-size: 14px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 8px rgba(34,113,177,0.3); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">';
            echo '<span class="dashicons dashicons-plus-alt" style="font-size: 18px; width: 18px; height: 18px;"></span>';
            echo esc_html__('Add User to Role', 'pexpress');
            echo '</button>';
            echo '</div>';

            echo '<div style="background: #f6f7f7; border-radius: 12px; overflow: hidden; border: 1px solid #dcdcde;">';
            echo '<table class="wp-list-table widefat fixed striped" style="margin: 0; border: none; background: #fff;">';
            echo '<thead>';
            echo '<tr>';
            echo '<th style="width: 200px; padding: 15px;">' . esc_html__('Role', 'pexpress') . '</th>';
            echo '<th style="padding: 15px;">' . esc_html__('Description', 'pexpress') . '</th>';
            echo '<th style="padding: 15px;">' . esc_html__('Assigned Users', 'pexpress') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($polar_roles as $role_key => $role_name) {
                $users = $role_users[$role_key];
                echo '<tr>';
                echo '<td style="padding: 15px;"><strong style="font-size: 15px; color: #1d2327;">' . esc_html($role_name) . '</strong></td>';
                echo '<td style="padding: 15px; color: #646970;">' . esc_html($this->get_role_description($role_key)) . '</td>';
                echo '<td style="padding: 15px;">';
                if (!empty($users)) {
                    echo '<div class="polar-users-list" style="display: flex; flex-direction: column; gap: 8px;">';
                    foreach ($users as $user) {
                        $user_polar_roles = array_intersect($user->roles, array_keys($polar_roles));
                        $other_roles = array_diff($user_polar_roles, array($role_key));

                        echo '<div class="polar-user-item" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; background: #f6f7f7; border-radius: 6px; border: 1px solid #dcdcde;">';
                        echo '<div style="flex: 1;">';
                        echo '<strong style="display: block; color: #1d2327; font-size: 14px; margin-bottom: 3px;">' . esc_html($user->display_name) . '</strong>';
                        echo '<span style="color: #646970; font-size: 12px;">' . esc_html($user->user_email) . '</span>';
                        if (!empty($other_roles)) {
                            $other_role_names = array();
                            foreach ($other_roles as $or) {
                                $other_role_names[] = $polar_roles[$or];
                            }
                            echo '<span style="display: block; color: #2271b1; font-size: 11px; margin-top: 4px; font-style: italic;">';
                            echo esc_html__('Also has:', 'pexpress') . ' ' . esc_html(implode(', ', $other_role_names));
                            echo '</span>';
                        }
                        echo '</div>';
                        echo '<button type="button" class="button button-small polar-remove-user-btn" data-role="' . esc_attr($role_key) . '" data-user-id="' . esc_attr($user->ID) . '" data-user-name="' . esc_attr($user->display_name) . '" style="margin-left: 10px; color: #b32d2e; border-color: #b32d2e;">';
                        echo '<span class="dashicons dashicons-dismiss" style="font-size: 16px; width: 16px; height: 16px; line-height: 1.2;"></span> ' . esc_html__('Remove', 'pexpress');
                        echo '</button>';
                        echo '</div>';
                    }
                    echo '<p style="margin: 10px 0 0 0; font-size: 12px; color: #646970; font-style: italic;">' . sprintf(esc_html__('Total: %d user(s)', 'pexpress'), count($users)) . '</p>';
                    echo '</div>';
                } else {
                    echo '<div style="padding: 30px; text-align: center; color: #999; background: #f6f7f7; border-radius: 6px; border: 2px dashed #dcdcde;">';
                    echo '<span class="dashicons dashicons-groups" style="font-size: 48px; width: 48px; height: 48px; display: block; margin: 0 auto 15px; opacity: 0.5;"></span>';
                    echo '<p style="margin: 0; font-size: 14px; font-weight: 500;">' . esc_html__('No users assigned', 'pexpress') . '</p>';
                    echo '<p style="margin: 5px 0 0 0; font-size: 12px;">' . esc_html__('Click "Add User to Role" to assign users', 'pexpress') . '</p>';
                    echo '</div>';
                }
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            $roles->render_add_user_modal($polar_roles, $all_users);
        }

        // SMS Settings Tab
        if ($current_tab === 'sms') {
            echo '<div class="pexpress-tab-panel" id="tab-sms">';
            echo '<form method="post" action="options.php" class="pexpress-settings-form">';
            settings_fields('pexpress_settings_group');
            echo '<div class="polar-tasks-section" style="background: #fff; padding: 32px; margin: 0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">';
            echo '<div class="polar-section-header" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h2 class="polar-section-title" style="font-size: 24px; font-weight: 700; color: #1d2327; margin: 0; display: flex; align-items: center; gap: 12px;">';
            echo '<span class="dashicons dashicons-smartphone" style="color: #00a32a; font-size: 28px; width: 28px; height: 28px;"></span>';
            echo esc_html__('SMS Configuration', 'pexpress');
            echo '</h2>';
            echo '<p style="color: #646970; margin: 8px 0 0 0; font-size: 14px;">' . esc_html__('Configure SMS notifications and API settings', 'pexpress') . '</p>';
            echo '</div>';
            echo '<div class="pexpress-settings-content">';
            $this->modules['sms']->render_section();
            echo '</div>';
            echo '<input type="hidden" name="pexpress_settings_tab" value="sms" />';
            echo '<div style="margin-top: 30px; padding-top: 24px; border-top: 2px solid #f0f0f1; display: flex; justify-content: flex-end;">';
            submit_button(__('Save SMS Settings', 'pexpress'), 'primary', 'submit', true, array('style' => 'background: linear-gradient(135deg, #00a32a 0%, #007a20 100%); border: none; padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,163,42,0.3); transition: all 0.3s ease;'));
            echo '</div>';
            echo '</div>';
            echo '</form>';
            echo '</div>';
        }

        // Email Settings Tab
        if ($current_tab === 'email') {
            echo '<div class="pexpress-tab-panel" id="tab-email">';
            echo '<form method="post" action="options.php" class="pexpress-settings-form">';
            settings_fields('pexpress_settings_group');
            echo '<div class="polar-tasks-section" style="background: #fff; padding: 32px; margin: 0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">';
            echo '<div class="polar-section-header" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h2 class="polar-section-title" style="font-size: 24px; font-weight: 700; color: #1d2327; margin: 0; display: flex; align-items: center; gap: 12px;">';
            echo '<span class="dashicons dashicons-email-alt" style="color: #d63638; font-size: 28px; width: 28px; height: 28px;"></span>';
            echo esc_html__('Email Configuration', 'pexpress');
            echo '</h2>';
            echo '<p style="color: #646970; margin: 8px 0 0 0; font-size: 14px;">' . esc_html__('Configure email notifications and Mailgun settings', 'pexpress') . '</p>';
            echo '</div>';
            echo '<div class="pexpress-settings-content">';
            $this->modules['email']->render_section();
            echo '</div>';
            echo '<input type="hidden" name="pexpress_settings_tab" value="email" />';
            echo '<div style="margin-top: 30px; padding-top: 24px; border-top: 2px solid #f0f0f1; display: flex; justify-content: flex-end;">';
            submit_button(__('Save Email Settings', 'pexpress'), 'primary', 'submit', true, array('style' => 'background: linear-gradient(135deg, #d63638 0%, #b32d2e 100%); border: none; padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 8px rgba(214,54,56,0.3); transition: all 0.3s ease;'));
            echo '</div>';
            echo '</div>';
            echo '</form>';
            echo '</div>';
        }

        // Templates Tab
        if ($current_tab === 'templates') {
            echo '<div class="pexpress-tab-panel" id="tab-templates">';
            echo '<form method="post" action="options.php" class="pexpress-settings-form">';
            settings_fields('pexpress_settings_group');

            // Email Templates
            echo '<div class="polar-tasks-section" style="background: #fff; padding: 32px; margin: 0 0 24px 0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">';
            echo '<div class="polar-section-header" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h2 class="polar-section-title" style="font-size: 24px; font-weight: 700; color: #1d2327; margin: 0; display: flex; align-items: center; gap: 12px;">';
            echo '<span class="dashicons dashicons-email-alt" style="color: #ffb900; font-size: 28px; width: 28px; height: 28px;"></span>';
            echo esc_html__('Email Templates', 'pexpress');
            echo '</h2>';
            echo '<p style="color: #646970; margin: 8px 0 0 0; font-size: 14px;">' . esc_html__('Customize email notification templates for order updates', 'pexpress') . '</p>';
            echo '</div>';
            echo '<div class="pexpress-settings-content">';
            $this->modules['templates']->render_email_templates_section();
            echo '</div>';
            echo '</div>';

            // SMS Templates
            echo '<div class="polar-tasks-section" style="background: #fff; padding: 32px; margin: 0 0 24px 0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">';
            echo '<div class="polar-section-header" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h2 class="polar-section-title" style="font-size: 24px; font-weight: 700; color: #1d2327; margin: 0; display: flex; align-items: center; gap: 12px;">';
            echo '<span class="dashicons dashicons-smartphone" style="color: #ffb900; font-size: 28px; width: 28px; height: 28px;"></span>';
            echo esc_html__('SMS Templates', 'pexpress');
            echo '</h2>';
            echo '<p style="color: #646970; margin: 8px 0 0 0; font-size: 14px;">' . esc_html__('Customize SMS notification templates for order updates', 'pexpress') . '</p>';
            echo '</div>';
            echo '<div class="pexpress-settings-content">';
            $this->modules['templates']->render_sms_templates_section();
            echo '</div>';
            echo '</div>';

            echo '<input type="hidden" name="pexpress_settings_tab" value="templates" />';
            echo '<div style="display: flex; justify-content: flex-end; margin-top: 0;">';
            submit_button(__('Save Templates', 'pexpress'), 'primary', 'submit', true, array('style' => 'background: linear-gradient(135deg, #ffb900 0%, #dba617 100%); border: none; padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 8px rgba(255,185,0,0.3); color: #1d2327; transition: all 0.3s ease;'));
            echo '</div>';
            echo '</form>';
            echo '</div>';
        }

        // General Settings Tab
        if ($current_tab === 'general') {
            echo '<div class="pexpress-tab-panel" id="tab-general">';
            echo '<form method="post" action="options.php" class="pexpress-settings-form">';
            settings_fields('pexpress_settings_group');
            echo '<div class="polar-tasks-section" style="background: #fff; padding: 32px; margin: 0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">';
            echo '<div class="polar-section-header" style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h2 class="polar-section-title" style="font-size: 24px; font-weight: 700; color: #1d2327; margin: 0; display: flex; align-items: center; gap: 12px;">';
            echo '<span class="dashicons dashicons-admin-settings" style="color: #8c8f94; font-size: 28px; width: 28px; height: 28px;"></span>';
            echo esc_html__('General Settings', 'pexpress');
            echo '</h2>';
            echo '<p style="color: #646970; margin: 8px 0 0 0; font-size: 14px;">' . esc_html__('Configure general plugin settings and preferences', 'pexpress') . '</p>';
            echo '</div>';
            echo '<div class="pexpress-settings-content">';
            $this->modules['general']->render_section();
            echo '</div>';
            echo '<input type="hidden" name="pexpress_settings_tab" value="general" />';
            echo '<div style="margin-top: 30px; padding-top: 24px; border-top: 2px solid #f0f0f1; display: flex; justify-content: flex-end;">';
            submit_button(__('Save General Settings', 'pexpress'), 'primary', 'submit', true, array('style' => 'background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); border: none; padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 8px rgba(34,113,177,0.3); transition: all 0.3s ease;'));
            echo '</div>';
            echo '</div>';
            echo '</form>';
            echo '</div>';
        }

        echo '</div>'; // End tab content
        echo '</div>'; // End wrap

        // Add inline JavaScript for modal and AJAX
        $roles->render_settings_scripts();

        // Add tab styling and scripts
        $this->render_settings_styles_and_scripts();
    }


    /**
     * Render settings styles and scripts
     */
    private function render_settings_styles_and_scripts()
    {
?>
        <style>
            .pexpress-settings-wrap {
                max-width: 1400px;
            }

            .pexpress-nav-tabs {
                background: linear-gradient(135deg, #f6f7f7 0%, #ffffff 100%);
                padding: 0 20px;
                margin: 0 -20px 0 -20px;
                border-bottom: 3px solid #2271b1;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .pexpress-nav-tabs .nav-tab {
                padding: 15px 24px;
                font-size: 14px;
                font-weight: 600;
                border: none;
                border-bottom: 3px solid transparent;
                background: transparent;
                color: #646970;
                margin: 0 2px 0 0;
                transition: all 0.3s ease;
                position: relative;
                border-radius: 6px 6px 0 0;
            }

            .pexpress-nav-tabs .nav-tab:hover {
                color: #2271b1;
                background: rgba(34, 113, 177, 0.08);
                transform: translateY(-2px);
            }

            .pexpress-nav-tabs .nav-tab-active {
                color: #2271b1;
                border-bottom-color: #2271b1;
                background: #ffffff;
                box-shadow: 0 -2px 8px rgba(34, 113, 177, 0.15);
            }

            .pexpress-nav-tabs .nav-tab[data-tab="roles"]:hover,
            .pexpress-nav-tabs .nav-tab[data-tab="roles"].nav-tab-active {
                color: #2271b1;
                border-bottom-color: #2271b1;
            }

            .pexpress-nav-tabs .nav-tab[data-tab="sms"]:hover,
            .pexpress-nav-tabs .nav-tab[data-tab="sms"].nav-tab-active {
                color: #00a32a;
                border-bottom-color: #00a32a;
            }

            .pexpress-nav-tabs .nav-tab[data-tab="email"]:hover,
            .pexpress-nav-tabs .nav-tab[data-tab="email"].nav-tab-active {
                color: #d63638;
                border-bottom-color: #d63638;
            }

            .pexpress-nav-tabs .nav-tab[data-tab="templates"]:hover,
            .pexpress-nav-tabs .nav-tab[data-tab="templates"].nav-tab-active {
                color: #ffb900;
                border-bottom-color: #ffb900;
            }

            .pexpress-nav-tabs .nav-tab[data-tab="general"]:hover,
            .pexpress-nav-tabs .nav-tab[data-tab="general"].nav-tab-active {
                color: #8c8f94;
                border-bottom-color: #8c8f94;
            }

            .pexpress-tab-content {
                background: transparent;
                padding: 0;
                margin: 0;
                min-height: 500px;
            }

            .pexpress-tab-panel {
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .pexpress-settings-content {
                padding: 0;
            }

            .pexpress-settings-form .form-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                margin: 0;
            }

            .pexpress-settings-form .form-table th {
                width: 280px;
                padding: 20px 24px 20px 0;
                font-weight: 600;
                color: #1d2327;
                font-size: 14px;
                vertical-align: top;
                border-bottom: 1px solid #f0f0f1;
            }

            .pexpress-settings-form .form-table td {
                padding: 20px 24px;
                border-bottom: 1px solid #f0f0f1;
                vertical-align: top;
            }

            .pexpress-settings-form .form-table tr:last-child th,
            .pexpress-settings-form .form-table tr:last-child td {
                border-bottom: none;
            }

            .pexpress-settings-form .form-table input[type="text"],
            .pexpress-settings-form .form-table input[type="email"],
            .pexpress-settings-form .form-table input[type="password"],
            .pexpress-settings-form .form-table input[type="url"],
            .pexpress-settings-form .form-table input[type="number"],
            .pexpress-settings-form .form-table select,
            .pexpress-settings-form .form-table textarea {
                width: 100%;
                max-width: 500px;
                padding: 10px 14px;
                border: 1px solid #8c8f94;
                border-radius: 6px;
                font-size: 14px;
                transition: all 0.2s ease;
                background: #fff;
            }

            .pexpress-settings-form input[type="text"]:focus,
            .pexpress-settings-form input[type="email"]:focus,
            .pexpress-settings-form input[type="password"]:focus,
            .pexpress-settings-form input[type="url"]:focus,
            .pexpress-settings-form input[type="number"]:focus,
            .pexpress-settings-form select:focus,
            .pexpress-settings-form textarea:focus {
                border-color: #2271b1 !important;
                outline: none;
                box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1) !important;
            }

            .pexpress-form-fields-container {
                display: flex;
                flex-direction: column;
                gap: 0;
            }

            .pexpress-form-field-wrapper {
                position: relative;
            }

            .pexpress-form-field-wrapper input[type="checkbox"]:checked+label,
            .pexpress-form-field-wrapper input[type="checkbox"]:checked~div {
                border-color: #2271b1;
            }

            .pexpress-settings-form .form-table input[type="checkbox"] {
                width: 18px;
                height: 18px;
                margin-right: 8px;
                cursor: pointer;
            }

            .pexpress-settings-form .description {
                margin-top: 10px;
                color: #646970;
                font-size: 13px;
                line-height: 1.6;
                display: block;
            }

            .pexpress-settings-form .button-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
            }

            .pexpress-settings-wrap.polar-dashboard {
                max-width: 1200px;
                margin: 20px auto;
                padding: 20px;
            }

            .pexpress-settings-wrap .polar-dashboard-header {
                position: relative;
                overflow: hidden;
            }

            .pexpress-settings-wrap .polar-dashboard-header::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
                pointer-events: none;
            }

            @media (max-width: 782px) {
                .pexpress-nav-tabs .nav-tab {
                    display: block;
                    width: 100%;
                    margin: 0 0 5px 0;
                    text-align: left;
                }

                .pexpress-settings-form .form-table th,
                .pexpress-settings-form .form-table td {
                    display: block;
                    width: 100%;
                    padding: 15px;
                    border-bottom: 1px solid #f0f0f1;
                }

                .pexpress-settings-form .form-table th {
                    border-bottom: none;
                    padding-bottom: 5px;
                }

                .polar-tasks-section {
                    padding: 20px !important;
                }
            }
        </style>
<?php
    }
}
