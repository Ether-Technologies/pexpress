<?php

/**
 * Admin functionality - Main orchestrator
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class - Main orchestrator
 */
class PExpress_Admin
{

    /**
     * Admin modules
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
        $this->init();
    }

    /**
     * Load admin modules
     */
    private function load_modules()
    {
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-menus.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-settings.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-dashboards.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-roles.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-pages.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-admin-order-manipulation.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-woocommerce-capabilities.php';
        require_once PEXPRESS_PLUGIN_DIR . 'admin/class-pexpress-role-capabilities.php';

        $this->modules['menus'] = new PExpress_Admin_Menus();
        $this->modules['settings'] = new PExpress_Admin_Settings();
        $this->modules['dashboards'] = new PExpress_Admin_Dashboards();
        $this->modules['roles'] = new PExpress_Admin_Roles();
        $this->modules['pages'] = new PExpress_Admin_Pages();
        $this->modules['order_manipulation'] = new PExpress_Admin_Order_Manipulation();
    }

    /**
     * Initialize admin functionality
     */
    private function init()
    {
        add_action('admin_menu', array($this->modules['menus'], 'register_menus'));
        add_action('admin_init', array($this->modules['menus'], 'redirect_wrong_page_slugs'));
        add_action('admin_footer', array($this->modules['menus'], 'fix_menu_links_js'));
        add_action('admin_init', array($this->modules['settings'], 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        // Fallback to ensure CSS loads for all users - use admin_print_styles as backup
        add_action('admin_print_styles', array($this, 'enqueue_admin_assets_fallback'));
        add_action('admin_post_pexpress_assign_role', array($this->modules['roles'], 'handle_role_assignment'));
        add_action('admin_post_pexpress_add_user_to_role', array($this->modules['roles'], 'handle_add_user_to_role'));
        add_action('admin_post_pexpress_remove_user_from_role', array($this->modules['roles'], 'handle_remove_user_from_role'));
        add_action('admin_notices', array($this->modules['roles'], 'show_role_assignment_notices'));
        add_action('wp_ajax_pexpress_get_users_for_role', array($this->modules['roles'], 'ajax_get_users_for_role'));
        add_action('wp_ajax_pexpress_send_test_email', array($this, 'ajax_send_test_email'));
        add_action('wp_ajax_pexpress_send_test_sms', array($this, 'ajax_send_test_sms'));

        // Filter admin page title to show correct title
        add_filter('admin_title', array($this, 'filter_admin_page_title'), 10, 2);
    }

    /**
     * Filter admin page title to show correct dashboard name
     */
    public function filter_admin_page_title($admin_title, $title)
    {
        $screen = get_current_screen();
        if (!$screen) {
            return $admin_title;
        }

        // Only filter Polar Express pages
        if (strpos($screen->id, 'polar-express') === false) {
            return $admin_title;
        }

        $current_user = wp_get_current_user();
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

        // Set correct title based on page
        if ($page === 'polar-express') {
            if (in_array('polar_hr', $current_user->roles) || current_user_can('manage_woocommerce')) {
                return __('Agency Dashboard', 'pexpress') . $title;
            } elseif (in_array('polar_delivery', $current_user->roles)) {
                return __('HR Dashboard', 'pexpress') . $title;
            } elseif (in_array('polar_support', $current_user->roles)) {
                return __('Support Portal', 'pexpress') . $title;
            }
        } elseif ($page === 'polar-express-delivery') {
            return __('HR Dashboard', 'pexpress') . $title;
        } elseif ($page === 'polar-express-support') {
            return __('Support Portal', 'pexpress') . $title;
        }

        return $admin_title;
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook = '')
    {
        // Check if we're on a Polar Express page
        $is_polar_page = false;

        // Check hook name (WordPress uses formats like 'toplevel_page_polar-express' or 'polar-express_page_polar-express-settings')
        if (!empty($hook) && (strpos($hook, 'polar-express') !== false || strpos($hook, 'polar_express') !== false)) {
            $is_polar_page = true;
        }

        // Fallback: Check page parameter from URL (most reliable)
        if (!$is_polar_page && isset($_GET['page'])) {
            $page = sanitize_text_field(wp_unslash($_GET['page']));
            if (strpos($page, 'polar-express') !== false || strpos($page, 'polar_express') !== false) {
                $is_polar_page = true;
            }
        }

        // Also check screen ID if available
        if (!$is_polar_page) {
            $screen = get_current_screen();
            if ($screen && isset($screen->id) && (strpos($screen->id, 'polar-express') !== false || strpos($screen->id, 'polar_express') !== false)) {
                $is_polar_page = true;
            }
        }

        if (!$is_polar_page) {
            return;
        }

        // Enqueue modern admin styles for all admin pages - no capability check needed
        wp_enqueue_style(
            'pexpress-admin-modern',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar-admin-modern.css',
            array(),
            PEXPRESS_VERSION
        );

        // Enqueue original styles for frontend dashboards (if needed)
        wp_enqueue_style(
            'pexpress-admin',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar.css',
            array(),
            PEXPRESS_VERSION
        );

        // Enqueue setup wizard script if on setup wizard page
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if (strpos($hook, 'polar-express-setup-wizard') !== false || $page === 'polar-express-setup-wizard') {
            wp_enqueue_script(
                'pexpress-admin-setup',
                PEXPRESS_PLUGIN_URL . 'assets/js/polar-admin-setup.js',
                array('jquery'),
                PEXPRESS_VERSION,
                true
            );
        }

        wp_enqueue_script(
            'pexpress-admin',
            PEXPRESS_PLUGIN_URL . 'assets/js/polar.js',
            array('jquery', 'heartbeat'),
            PEXPRESS_VERSION,
            true
        );

        wp_localize_script(
            'pexpress-admin',
            'polarExpress',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('polar_express_nonce'),
                'heartbeatInterval' => get_option('pexpress_options')['heartbeat_interval'] ?? 15
            )
        );
    }

    /**
     * Fallback method to ensure CSS loads even if admin_enqueue_scripts hook fails
     * This ensures CSS loads for all users regardless of capabilities
     */
    public function enqueue_admin_assets_fallback()
    {
        // Only run if assets weren't already enqueued
        if (wp_style_is('pexpress-admin-modern', 'enqueued')) {
            return;
        }

        // Check if we're on a Polar Express page
        $is_polar_page = false;

        // Check page parameter from URL (most reliable method)
        if (isset($_GET['page'])) {
            $page = sanitize_text_field(wp_unslash($_GET['page']));
            if (strpos($page, 'polar-express') !== false || strpos($page, 'polar_express') !== false) {
                $is_polar_page = true;
            }
        }

        // Also check screen ID if available
        if (!$is_polar_page) {
            $screen = get_current_screen();
            if ($screen && isset($screen->id) && (strpos($screen->id, 'polar-express') !== false || strpos($screen->id, 'polar_express') !== false)) {
                $is_polar_page = true;
            }
        }

        if (!$is_polar_page) {
            return;
        }

        // Enqueue styles using wp_enqueue_style even in admin_head
        wp_enqueue_style(
            'pexpress-admin-modern',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar-admin-modern.css',
            array(),
            PEXPRESS_VERSION
        );

        wp_enqueue_style(
            'pexpress-admin',
            PEXPRESS_PLUGIN_URL . 'assets/css/polar.css',
            array(),
            PEXPRESS_VERSION
        );

        wp_enqueue_script(
            'pexpress-admin',
            PEXPRESS_PLUGIN_URL . 'assets/js/polar.js',
            array('jquery', 'heartbeat'),
            PEXPRESS_VERSION,
            true
        );

        wp_localize_script(
            'pexpress-admin',
            'polarExpress',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('polar_express_nonce'),
                'heartbeatInterval' => get_option('pexpress_options')['heartbeat_interval'] ?? 15
            )
        );
    }

    /**
     * AJAX handler: Send test email
     */
    public function ajax_send_test_email()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pexpress_test_mail_ajax')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'pexpress')));
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('You do not have permission to send test emails.', 'pexpress')));
        }

        // Get form data
        $to = isset($_POST['to']) ? sanitize_email($_POST['to']) : '';
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $message = isset($_POST['message']) ? wp_kses_post($_POST['message']) : '';

        // Validate inputs
        if (empty($to) || !is_email($to)) {
            wp_send_json_error(array('message' => __('Please enter a valid email address.', 'pexpress')));
        }

        if (empty($subject)) {
            wp_send_json_error(array('message' => __('Please enter an email subject.', 'pexpress')));
        }

        if (empty($message)) {
            wp_send_json_error(array('message' => __('Please enter an email message.', 'pexpress')));
        }

        // Check if email is enabled
        $options = get_option('pexpress_options', array());
        $email_config = isset($options['email_config']) ? $options['email_config'] : array();

        if (empty($email_config['enable_email'])) {
            wp_send_json_error(array(
                'message' => __('Email notifications are disabled. Please enable them in Settings → Email Configuration.', 'pexpress')
            ));
        }

        // Replace placeholders in message
        $mailgun_config = isset($options['mailgun_config']) ? $options['mailgun_config'] : array();
        $use_mailgun = !empty($mailgun_config['enable_mailgun']) && class_exists('PExpress_Mailgun');

        $method = $use_mailgun ? 'Mailgun' : 'wp_mail';
        $message = str_replace('{{method}}', $method, $message);
        $message = str_replace('{{time}}', current_time('mysql'), $message);
        $message = str_replace('{{site}}', get_bloginfo('name'), $message);

        // Convert to HTML
        $html_message = '<html><body>';
        $html_message .= '<p>' . nl2br(esc_html($message)) . '</p>';
        $html_message .= '</body></html>';

        // Send email using PExpress_Email class
        if (!class_exists('PExpress_Email')) {
            wp_send_json_error(array('message' => __('Email class not found. Please ensure the plugin is properly installed.', 'pexpress')));
        }

        // Send the email
        $result = PExpress_Email::send_email($to, $subject, $html_message);

        // Handle result
        if (is_wp_error($result)) {
            $error_code = $result->get_error_code();
            $error_message = $result->get_error_message();
            $error_data = $result->get_error_data();

            // Build detailed error message
            $detailed_message = $error_message;

            // Add more context based on error code
            if ($error_code === 'email_disabled') {
                $detailed_message = __('Email notifications are disabled. Please enable them in Settings → Email Configuration.', 'pexpress');
            } elseif ($error_code === 'invalid_email') {
                $detailed_message = __('Invalid email address. Please check the recipient email.', 'pexpress');
            } elseif ($error_code === 'mailgun_not_configured') {
                $detailed_message = __('Mailgun is enabled but not properly configured. Please check your Mailgun API key and domain in Settings.', 'pexpress');
            } elseif ($error_code === 'mailgun_api_error' && isset($error_data['response_code'])) {
                $detailed_message = sprintf(
                    __('Mailgun API error (Code: %d). %s', 'pexpress'),
                    $error_data['response_code'],
                    $error_message
                );
            } elseif ($error_code === 'email_send_failed') {
                $detailed_message = __('Failed to send email. This could be due to server configuration issues. Check your WordPress mail settings or use Mailgun for better deliverability.', 'pexpress');
            }

            wp_send_json_error(array('message' => $detailed_message));
        }

        // Check if result is false (wp_mail can return false without WP_Error)
        if ($result === false) {
            wp_send_json_error(array(
                'message' => __('Email sending failed. wp_mail returned false. This is usually a server configuration issue. Consider using Mailgun for better deliverability.', 'pexpress')
            ));
        }

        // Success
        wp_send_json_success(array(
            'message' => sprintf(
                __('Test email sent successfully to %s using %s method.', 'pexpress'),
                esc_html($to),
                esc_html($method)
            )
        ));
    }

    /**
     * AJAX handler: Send test SMS
     */
    public function ajax_send_test_sms()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pexpress_test_sms_ajax')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'pexpress')));
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('You do not have permission to send test SMS.', 'pexpress')));
        }

        // Get form data
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        // Validate inputs
        if (empty($phone)) {
            wp_send_json_error(array('message' => __('Please enter a phone number.', 'pexpress')));
        }

        if (empty($message)) {
            wp_send_json_error(array('message' => __('Please enter an SMS message.', 'pexpress')));
        }

        // Check if SMS is enabled
        $options = get_option('pexpress_options', array());
        $sms_config = isset($options['sms_config']) ? $options['sms_config'] : array();

        if (empty($sms_config['enable_plugin'])) {
            wp_send_json_error(array(
                'message' => __('SMS notifications are disabled. Please enable them in Settings → SMS Configuration.', 'pexpress')
            ));
        }

        // Check API configuration
        $api_token = isset($sms_config['api_hash_token']) ? $sms_config['api_hash_token'] : '';
        $api_sid = isset($sms_config['api_sid']) ? $sms_config['api_sid'] : '';

        if (empty($api_token)) {
            wp_send_json_error(array(
                'message' => __('SMS API Token is not configured. Please set it in Settings → SMS Configuration.', 'pexpress')
            ));
        }

        if (empty($api_sid)) {
            wp_send_json_error(array(
                'message' => __('SMS SID/Stakeholder is not configured. Please set it in Settings → SMS Configuration.', 'pexpress')
            ));
        }

        // Replace placeholders in message
        $message = str_replace('{{time}}', current_time('mysql'), $message);
        $message = str_replace('{{site}}', get_bloginfo('name'), $message);

        // Send SMS
        if (!function_exists('polar_send_sms')) {
            wp_send_json_error(array('message' => __('SMS function not found. Please ensure the plugin is properly installed.', 'pexpress')));
        }

        $result = polar_send_sms($phone, $message);

        // Handle result
        if (is_wp_error($result)) {
            $error_code = $result->get_error_code();
            $error_message = $result->get_error_message();
            $error_data = $result->get_error_data();

            // Build detailed error message
            $detailed_message = $error_message;

            // Add more context based on error code
            if ($error_code === 'sms_disabled') {
                $detailed_message = __('SMS notifications are disabled. Please enable them in Settings → SMS Configuration.', 'pexpress');
            } elseif ($error_code === 'invalid_phone') {
                $detailed_message = __('Invalid phone number. Please check the phone number format.', 'pexpress');
            } elseif ($error_code === 'sms_config_error') {
                $detailed_message = __('SMS API configuration error. Please check your API Token and SID in Settings.', 'pexpress');
            } elseif ($error_code === 'sms_send_failed') {
                $detailed_message = __('Failed to send SMS. Please check your SMS API credentials and try again.', 'pexpress');
                if (is_array($error_data) && isset($error_data[1])) {
                    $body = json_decode($error_data[1], true);
                    if (is_array($body) && isset($body['message'])) {
                        $detailed_message .= ' API Response: ' . $body['message'];
                    }
                }
            }

            wp_send_json_error(array('message' => $detailed_message));
        }

        // Check if result is false
        if ($result === false) {
            wp_send_json_error(array(
                'message' => __('SMS sending failed. Please check your SMS configuration and try again.', 'pexpress')
            ));
        }

        // Success
        wp_send_json_success(array(
            'message' => sprintf(
                __('Test SMS sent successfully to %s.', 'pexpress'),
                esc_html($phone)
            )
        ));
    }
}
