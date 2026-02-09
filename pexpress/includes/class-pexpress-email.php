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
            // Mark as logged to prevent duplicate logging in wp_mail filter
            if (!defined('PEXPRESS_EMAIL_LOGGED')) {
                define('PEXPRESS_EMAIL_LOGGED', true);
            }
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
                PExpress_Email_Log::update_log($log_id, 'success', '', null, '', 'wp_mail');
            }
        }

        // Reset the logged flag after sending
        if (defined('PEXPRESS_EMAIL_LOGGED')) {
            // Can't undefine, but we'll check in the filter
        }

        if (!$result) {
            return new WP_Error('email_send_failed', __('Failed to send email.', 'pexpress'));
        }

        return true;
    }

    /**
     * Get order details for email template
     *
     * @param int|WC_Order $order Order ID or WC_Order object.
     * @return array Order details array
     */
    public static function get_order_details($order)
    {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }

        if (!$order || !is_a($order, 'WC_Order')) {
            return array();
        }

        // Basic order info
        $order_id = $order->get_id();
        $order_date = $order->get_date_created() ? $order->get_date_created()->date_i18n(get_option('date_format') . ' ' . get_option('time_format')) : '';
        $order_status = wc_get_order_status_name($order->get_status());
        $order_total = $order->get_formatted_order_total();
        $order_subtotal = wc_price($order->get_subtotal());
        $order_tax = wc_price($order->get_total_tax());
        $order_shipping = wc_price($order->get_shipping_total());
        $order_discount = wc_price($order->get_total_discount());

        // Customer info
        $customer_name = PExpress_Core::get_billing_name($order);
        $customer_email = $order->get_billing_email();
        $customer_phone = $order->get_billing_phone();

        // Billing address
        $billing_address = $order->get_formatted_billing_address();
        $billing_address_array = array(
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'company' => $order->get_billing_company(),
            'address_1' => $order->get_billing_address_1(),
            'address_2' => $order->get_billing_address_2(),
            'city' => $order->get_billing_city(),
            'state' => $order->get_billing_state(),
            'postcode' => $order->get_billing_postcode(),
            'country' => $order->get_billing_country(),
        );

        // Shipping address
        $shipping_address = $order->get_formatted_shipping_address();
        $shipping_address_array = array(
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

        // Payment info
        $payment_method = $order->get_payment_method_title();
        $payment_method_raw = $order->get_payment_method();
        $transaction_id = $order->get_transaction_id();

        // Order items
        $items_html = '';
        $items_text = '';
        $items = $order->get_items();

        if (!empty($items)) {
            $items_html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
            $items_html .= '<thead><tr style="background: #f5f5f5;">';
            $items_html .= '<th style="padding: 12px; text-align: left; border: 1px solid #ddd;">' . __('Product', 'pexpress') . '</th>';
            $items_html .= '<th style="padding: 12px; text-align: center; border: 1px solid #ddd;">' . __('Quantity', 'pexpress') . '</th>';
            $items_html .= '<th style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . __('Price', 'pexpress') . '</th>';
            $items_html .= '<th style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . __('Total', 'pexpress') . '</th>';
            $items_html .= '</tr></thead><tbody>';

            foreach ($items as $item) {
                $product = $item->get_product();
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $line_total = wc_price($item->get_total());
                $line_subtotal = wc_price($item->get_subtotal());

                $items_html .= '<tr>';
                $items_html .= '<td style="padding: 12px; border: 1px solid #ddd;">' . esc_html($product_name) . '</td>';
                $items_html .= '<td style="padding: 12px; text-align: center; border: 1px solid #ddd;">' . esc_html($quantity) . '</td>';
                $items_html .= '<td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . $line_subtotal . '</td>';
                $items_html .= '<td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . $line_total . '</td>';
                $items_html .= '</tr>';

                $items_text .= $product_name . ' x' . $quantity . ' - ' . $line_total . "\n";
            }

            $items_html .= '</tbody></table>';
        }

        // Order notes
        $order_notes = wc_get_order_notes(array('order_id' => $order_id));
        $notes_html = '';
        $notes_text = '';
        if (!empty($order_notes)) {
            $notes_html = '<div style="margin: 20px 0;"><h3 style="margin-bottom: 10px;">' . __('Order Notes', 'pexpress') . '</h3><ul>';
            foreach ($order_notes as $note) {
                $note_content = $note->comment_content;
                $note_date = $note->comment_date;
                $notes_html .= '<li style="margin-bottom: 10px;"><strong>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note_date)) . ':</strong> ' . esc_html($note_content) . '</li>';
                $notes_text .= date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note_date)) . ': ' . $note_content . "\n";
            }
            $notes_html .= '</ul></div>';
        }

        return array(
            'order_id' => $order_id,
            'order_number' => $order->get_order_number(),
            'order_date' => $order_date,
            'order_status' => $order_status,
            'order_total' => $order_total,
            'order_subtotal' => $order_subtotal,
            'order_tax' => $order_tax,
            'order_shipping' => $order_shipping,
            'order_discount' => $order_discount,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'billing_address' => $billing_address,
            'billing_first_name' => $billing_address_array['first_name'],
            'billing_last_name' => $billing_address_array['last_name'],
            'billing_company' => $billing_address_array['company'],
            'billing_address_1' => $billing_address_array['address_1'],
            'billing_address_2' => $billing_address_array['address_2'],
            'billing_city' => $billing_address_array['city'],
            'billing_state' => $billing_address_array['state'],
            'billing_postcode' => $billing_address_array['postcode'],
            'billing_country' => $billing_address_array['country'],
            'shipping_address' => $shipping_address,
            'shipping_first_name' => $shipping_address_array['first_name'],
            'shipping_last_name' => $shipping_address_array['last_name'],
            'shipping_company' => $shipping_address_array['company'],
            'shipping_address_1' => $shipping_address_array['address_1'],
            'shipping_address_2' => $shipping_address_array['address_2'],
            'shipping_city' => $shipping_address_array['city'],
            'shipping_state' => $shipping_address_array['state'],
            'shipping_postcode' => $shipping_address_array['postcode'],
            'shipping_country' => $shipping_address_array['country'],
            'payment_method' => $payment_method,
            'payment_method_raw' => $payment_method_raw,
            'transaction_id' => $transaction_id,
            'order_items_html' => $items_html,
            'order_items_text' => $items_text,
            'order_notes_html' => $notes_html,
            'order_notes_text' => $notes_text,
            'cancellation_reason' => PExpress_Core::get_order_meta($order->get_id(), '_polar_cancel_reason') ?: '',
        );
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
        // Get order details if order_id is provided
        $order_details = array();
        if (isset($data['order_id'])) {
            $order = wc_get_order($data['order_id']);
            if ($order) {
                $order_details = self::get_order_details($order);
            }
        }

        // Merge provided data with order details (provided data takes precedence)
        $all_data = array_merge($order_details, $data);

        // Basic placeholders
        $placeholders = array(
            '{{order_id}}' => isset($all_data['order_id']) ? $all_data['order_id'] : '',
            '{{order_number}}' => isset($all_data['order_number']) ? $all_data['order_number'] : (isset($all_data['order_id']) ? $all_data['order_id'] : ''),
            '{{customer_name}}' => isset($all_data['customer_name']) ? $all_data['customer_name'] : '',
            '{{customer_email}}' => isset($all_data['customer_email']) ? $all_data['customer_email'] : '',
            '{{customer_phone}}' => isset($all_data['customer_phone']) ? $all_data['customer_phone'] : '',
            '{{order_total}}' => isset($all_data['order_total']) ? $all_data['order_total'] : '',
            '{{order_subtotal}}' => isset($all_data['order_subtotal']) ? $all_data['order_subtotal'] : '',
            '{{order_tax}}' => isset($all_data['order_tax']) ? $all_data['order_tax'] : '',
            '{{order_shipping}}' => isset($all_data['order_shipping']) ? $all_data['order_shipping'] : '',
            '{{order_discount}}' => isset($all_data['order_discount']) ? $all_data['order_discount'] : '',
            '{{order_date}}' => isset($all_data['order_date']) ? $all_data['order_date'] : '',
            '{{order_status}}' => isset($all_data['order_status']) ? $all_data['order_status'] : '',
            '{{billing_address}}' => isset($all_data['billing_address']) ? $all_data['billing_address'] : '',
            '{{billing_first_name}}' => isset($all_data['billing_first_name']) ? $all_data['billing_first_name'] : '',
            '{{billing_last_name}}' => isset($all_data['billing_last_name']) ? $all_data['billing_last_name'] : '',
            '{{billing_company}}' => isset($all_data['billing_company']) ? $all_data['billing_company'] : '',
            '{{billing_address_1}}' => isset($all_data['billing_address_1']) ? $all_data['billing_address_1'] : '',
            '{{billing_address_2}}' => isset($all_data['billing_address_2']) ? $all_data['billing_address_2'] : '',
            '{{billing_city}}' => isset($all_data['billing_city']) ? $all_data['billing_city'] : '',
            '{{billing_state}}' => isset($all_data['billing_state']) ? $all_data['billing_state'] : '',
            '{{billing_postcode}}' => isset($all_data['billing_postcode']) ? $all_data['billing_postcode'] : '',
            '{{billing_country}}' => isset($all_data['billing_country']) ? $all_data['billing_country'] : '',
            '{{shipping_address}}' => isset($all_data['shipping_address']) ? $all_data['shipping_address'] : '',
            '{{shipping_first_name}}' => isset($all_data['shipping_first_name']) ? $all_data['shipping_first_name'] : '',
            '{{shipping_last_name}}' => isset($all_data['shipping_last_name']) ? $all_data['shipping_last_name'] : '',
            '{{shipping_company}}' => isset($all_data['shipping_company']) ? $all_data['shipping_company'] : '',
            '{{shipping_address_1}}' => isset($all_data['shipping_address_1']) ? $all_data['shipping_address_1'] : '',
            '{{shipping_address_2}}' => isset($all_data['shipping_address_2']) ? $all_data['shipping_address_2'] : '',
            '{{shipping_city}}' => isset($all_data['shipping_city']) ? $all_data['shipping_city'] : '',
            '{{shipping_state}}' => isset($all_data['shipping_state']) ? $all_data['shipping_state'] : '',
            '{{shipping_postcode}}' => isset($all_data['shipping_postcode']) ? $all_data['shipping_postcode'] : '',
            '{{shipping_country}}' => isset($all_data['shipping_country']) ? $all_data['shipping_country'] : '',
            '{{payment_method}}' => isset($all_data['payment_method']) ? $all_data['payment_method'] : '',
            '{{transaction_id}}' => isset($all_data['transaction_id']) ? $all_data['transaction_id'] : '',
            '{{order_items}}' => isset($all_data['order_items_html']) ? $all_data['order_items_html'] : '',
            '{{order_items_text}}' => isset($all_data['order_items_text']) ? $all_data['order_items_text'] : '',
            '{{order_notes}}' => isset($all_data['order_notes_html']) ? $all_data['order_notes_html'] : '',
            '{{order_notes_text}}' => isset($all_data['order_notes_text']) ? $all_data['order_notes_text'] : '',
            '{{cancellation_reason}}' => isset($all_data['cancellation_reason']) ? $all_data['cancellation_reason'] : '',
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
            'order_placed' => __('We have received your order #{{order_id}}. Thank you! We will confirm shortly.', 'pexpress'),
            'order_cancelled' => __('Your order #{{order_id}} has been cancelled. Reason: {{cancellation_reason}}', 'pexpress'),
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
     * Check if email is auto-generated from phone number
     *
     * @param string $email Email address to check.
     * @return bool True if email is auto-generated, false otherwise.
     */
    private static function is_auto_generated_email($email)
    {
        if (empty($email)) {
            return false;
        }

        $domain = parse_url(home_url(), PHP_URL_HOST);
        if (empty($domain)) {
            return false;
        }

        // Pattern: phone@domain or phone_counter@domain or phone_timestamp@domain
        // Phone is 11 digits starting with 01 (e.g., 01712345678)
        // Optional suffix: _ followed by digits (counter or timestamp)
        $pattern = '/^01[3-9][0-9]{8}(_\d+)?@' . preg_quote($domain, '/') . '$/';
        
        return (bool) preg_match($pattern, $email);
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
        $order = null;
        if (empty($to) && isset($data['order_id'])) {
            $order = wc_get_order($data['order_id']);
            if ($order) {
                $to = $order->get_billing_email();
            }
        }

        if (empty($to)) {
            return new WP_Error('no_email', __('No email address available.', 'pexpress'));
        }

        // Check if email is auto-generated
        // If billing_email is auto-generated, it means customer didn't provide a real email during checkout
        // Skip sending email notifications for auto-generated emails
        if (self::is_auto_generated_email($to)) {
            return new WP_Error('auto_generated_email', __('Email notification skipped for auto-generated email address.', 'pexpress'));
        }

        // Get template
        $template = self::get_template($template_key);
        if (empty($template)) {
            return new WP_Error('no_template', __('Email template not found.', 'pexpress'));
        }

        // Get order object if order_id is provided (for template processing)
        if (!$order && isset($data['order_id'])) {
            $order = wc_get_order($data['order_id']);
        }

        // Process template
        $message = self::process_template($template, $data);

        // Check if message already contains HTML (has tags)
        $is_html = (strip_tags($message) !== $message);

        // If not HTML, wrap in basic HTML structure
        if (!$is_html) {
            $html_message = '<html><body>';
            $html_message .= '<p>' . nl2br(esc_html($message)) . '</p>';
            $html_message .= '</body></html>';
        } else {
            // Message is already HTML, wrap in full HTML email structure
            $html_message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html__('Order Notification', 'pexpress') . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 20px 0;">
                <table role="presentation" style="width: 600px; margin: 0 auto; background-color: #ffffff; border-collapse: collapse; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 30px; background-color: #2271b1; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 24px;">' . esc_html(get_bloginfo('name')) . '</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            ' . $message . '
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f5f5f5; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #666;">
                            <p style="margin: 0;">' . sprintf(esc_html__('This is an automated email from %s. Please do not reply to this email.', 'pexpress'), esc_html(get_bloginfo('name'))) . '</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        }

        // Subject
        $subject = sprintf(__('Order #%s Update', 'pexpress'), isset($data['order_id']) ? $data['order_id'] : '');

        // Send email
        return self::send_email($to, $subject, $html_message);
    }
}
