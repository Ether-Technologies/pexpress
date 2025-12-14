<?php

/**
 * Email Notification System
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email notification handler class
 */
class PExpress_Email
{
    /**
     * Send email notification
     *
     * @param string $to      Recipient email address.
     * @param string $subject Email subject.
     * @param string $message Email message (HTML).
     * @param array  $headers Optional email headers.
     * @return bool|WP_Error
     */
    public static function send_email($to, $subject, $message, $headers = array())
    {
        // Check if email is enabled
        $options = get_option('pexpress_options', array());
        $email_config = isset($options['email_config']) ? $options['email_config'] : array();

        if (empty($email_config['enable_email'])) {
            return new WP_Error('email_disabled', __('Email notifications are disabled.', 'pexpress'));
        }

        // Validate email address
        if (!is_email($to)) {
            return new WP_Error('invalid_email', __('Invalid email address.', 'pexpress'));
        }

        // Set default headers
        $default_headers = array('Content-Type: text/html; charset=UTF-8');

        // Set from name and email
        $from_name = isset($email_config['from_name']) ? $email_config['from_name'] : get_bloginfo('name');
        $from_email = isset($email_config['from_email']) ? $email_config['from_email'] : get_option('admin_email');

        $default_headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

        $headers = array_merge($default_headers, $headers);

        // Check if Mailgun is enabled and configured
        $mailgun_config = isset($options['mailgun_config']) ? $options['mailgun_config'] : array();
        $use_mailgun = !empty($mailgun_config['enable_mailgun']) && class_exists('PExpress_Mailgun');

        // Log email attempt
        $log_id = null;
        if (class_exists('PExpress_Email_Log')) {
            $method = $use_mailgun ? 'mailgun' : 'wp_mail';
            $log_id = PExpress_Email_Log::log($to, $subject, $message, $headers, $method, 'pending');
        }

        if ($use_mailgun) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PExpress: Attempting to send email via Mailgun');
            }
            
            // Use Mailgun API
            $result = PExpress_Mailgun::send_email($to, $subject, $message, $headers);

            // Update log with result
            if ($log_id && class_exists('PExpress_Email_Log')) {
                if (is_wp_error($result)) {
                    $error_data = $result->get_error_data();
                    $response_code = isset($error_data['response_code']) ? $error_data['response_code'] : null;
                    $response_body = isset($error_data['response_body']) ? $error_data['response_body'] : '';
                    PExpress_Email_Log::update_log($log_id, 'failed', $result->get_error_message(), $response_code, $response_body);
                } else {
                    PExpress_Email_Log::update_log($log_id, 'success', '', 200);
                }
            }

            // If Mailgun fails, fallback to wp_mail
            if (is_wp_error($result)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('PExpress Mailgun Error: ' . $result->get_error_message());
                    error_log('PExpress: Falling back to wp_mail');
                }
                // Fallback to default wp_mail
                $result = wp_mail($to, $subject, $message, $headers);
                if (!$result) {
                    if ($log_id && class_exists('PExpress_Email_Log')) {
                        PExpress_Email_Log::update_log($log_id, 'failed', __('wp_mail fallback also failed.', 'pexpress'));
                    }
                    return new WP_Error('email_send_failed', __('Failed to send email.', 'pexpress'));
                }
                if ($log_id && class_exists('PExpress_Email_Log')) {
                    PExpress_Email_Log::update_log($log_id, 'success', '', null, '', 'wp_mail');
                }
                return true;
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PExpress: Email sent successfully via Mailgun');
            }
            return $result;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PExpress: Mailgun not enabled or class not found. Using wp_mail. Mailgun enabled: ' . (empty($mailgun_config['enable_mailgun']) ? 'no' : 'yes') . ', Class exists: ' . (class_exists('PExpress_Mailgun') ? 'yes' : 'no'));
            }
        }

        // Send email using default WordPress mail
        $result = wp_mail($to, $subject, $message, $headers);

        // Update log with result
        if ($log_id && class_exists('PExpress_Email_Log')) {
            if (!$result) {
                PExpress_Email_Log::update_log($log_id, 'failed', __('wp_mail returned false.', 'pexpress'));
            } else {
                PExpress_Email_Log::update_log($log_id, 'success');
            }
        }

        if (!$result) {
            return new WP_Error('email_send_failed', __('Failed to send email.', 'pexpress'));
        }

        return true;
    }

    /**
     * Process template with placeholders
     *
     * @param string $template Template string with placeholders.
     * @param array  $data     Data array for placeholders.
     * @return string Processed template
     */
    public static function process_template($template, $data)
    {
        $placeholders = array(
            '{{order_id}}' => isset($data['order_id']) ? $data['order_id'] : '',
            '{{customer_name}}' => isset($data['customer_name']) ? $data['customer_name'] : '',
            '{{order_total}}' => isset($data['order_total']) ? $data['order_total'] : '',
            '{{order_date}}' => isset($data['order_date']) ? $data['order_date'] : '',
        );

        $message = $template;
        foreach ($placeholders as $placeholder => $value) {
            $message = str_replace($placeholder, $value, $message);
        }

        return $message;
    }

    /**
     * Get email template
     *
     * @param string $template_key Template key.
     * @return string Template content
     */
    public static function get_template($template_key)
    {
        $options = get_option('pexpress_options', array());
        $email_templates = isset($options['email_templates']) ? $options['email_templates'] : array();

        // Default templates
        $default_templates = array(
            'order_confirmed' => __('Your order #{{order_id}} has been confirmed. Thank you for your order!', 'pexpress'),
            'order_proceeded' => __('Your order #{{order_id}} is now being processed. We will update you soon.', 'pexpress'),
            'out_for_delivery' => __('Your order #{{order_id}} is out for delivery. You will receive it shortly.', 'pexpress'),
            'order_completed' => __('Your order #{{order_id}} has been completed. Thank you for choosing us!', 'pexpress'),
        );

        if (isset($email_templates[$template_key]['template'])) {
            return $email_templates[$template_key]['template'];
        } elseif (isset($default_templates[$template_key])) {
            return $default_templates[$template_key];
        }

        return '';
    }

    /**
     * Check if template is enabled
     *
     * @param string $template_key Template key.
     * @return bool
     */
    public static function is_template_enabled($template_key)
    {
        $options = get_option('pexpress_options', array());
        $email_templates = isset($options['email_templates']) ? $options['email_templates'] : array();

        return !empty($email_templates[$template_key]['enabled']);
    }

    /**
     * Send notification email
     *
     * @param string $template_key Template key.
     * @param array  $data         Order data.
     * @param string $to          Recipient email (optional, will use order email if not provided).
     * @return bool|WP_Error
     */
    public static function send_notification($template_key, $data, $to = '')
    {
        // Check if template is enabled
        if (!self::is_template_enabled($template_key)) {
            return new WP_Error('template_disabled', __('This email template is disabled.', 'pexpress'));
        }

        // Get recipient email
        if (empty($to) && isset($data['order_id'])) {
            $order = wc_get_order($data['order_id']);
            if ($order) {
                $to = $order->get_billing_email();
            }
        }

        if (empty($to)) {
            return new WP_Error('no_email', __('No email address available.', 'pexpress'));
        }

        // Get template
        $template = self::get_template($template_key);
        if (empty($template)) {
            return new WP_Error('no_template', __('Email template not found.', 'pexpress'));
        }

        // Process template
        $message = self::process_template($template, $data);

        // Convert to HTML
        $html_message = '<html><body>';
        $html_message .= '<p>' . nl2br(esc_html($message)) . '</p>';
        $html_message .= '</body></html>';

        // Subject
        $subject = sprintf(__('Order #%s Update', 'pexpress'), isset($data['order_id']) ? $data['order_id'] : '');

        // Send email
        return self::send_email($to, $subject, $html_message);
    }
}
