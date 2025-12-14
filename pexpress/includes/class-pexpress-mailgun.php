<?php

/**
 * Mailgun API Integration
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mailgun API integration class
 */
class PExpress_Mailgun
{
    /**
     * Get Mailgun configuration
     *
     * @return array|false Configuration array or false if not configured
     */
    private static function get_config()
    {
        $options = get_option('pexpress_options', array());
        $mailgun_config = isset($options['mailgun_config']) ? $options['mailgun_config'] : array();

        if (empty($mailgun_config['enable_mailgun'])) {
            return false;
        }

        $api_key = isset($mailgun_config['api_key']) ? trim($mailgun_config['api_key']) : '';
        $domain = isset($mailgun_config['domain']) ? trim($mailgun_config['domain']) : '';
        $region = isset($mailgun_config['region']) ? trim($mailgun_config['region']) : 'us';

        if (empty($api_key) || empty($domain)) {
            return false;
        }

        return array(
            'api_key' => $api_key,
            'domain' => $domain,
            'region' => $region,
        );
    }

    /**
     * Get API endpoint based on region
     *
     * @param string $region Region (us or eu)
     * @return string API endpoint URL
     */
    private static function get_api_endpoint($region)
    {
        if ($region === 'eu') {
            return 'https://api.eu.mailgun.net/v3/';
        }
        return 'https://api.mailgun.net/v3/';
    }

    /**
     * Parse email headers
     *
     * @param string|array $headers Email headers
     * @return array Parsed headers with from_name, from_email, cc, bcc, content_type, charset, and other headers
     */
    private static function parse_headers($headers)
    {
        $result = array(
            'from_name' => null,
            'from_email' => null,
            'cc' => array(),
            'bcc' => array(),
            'content_type' => null,
            'charset' => null,
            'headers' => array(),
        );

        if (empty($headers)) {
            return $result;
        }

        if (!is_array($headers)) {
            $headers = explode("\n", str_replace("\r\n", "\n", $headers));
        }

        foreach ($headers as $header) {
            if (strpos($header, ':') === false) {
                continue;
            }

            list($name, $content) = explode(':', trim($header), 2);
            $name = trim($name);
            $content = trim($content);

            switch (strtolower($name)) {
                case 'from':
                    if (strpos($content, '<') !== false) {
                        $result['from_name'] = trim(substr($content, 0, strpos($content, '<') - 1), ' "');
                        $result['from_email'] = trim(str_replace(array('>', '<'), '', substr($content, strpos($content, '<') + 1)));
                    } else {
                        $result['from_email'] = trim($content);
                    }
                    break;

                case 'content-type':
                    if (strpos($content, ';') !== false) {
                        list($type, $charset_part) = explode(';', $content, 2);
                        $result['content_type'] = trim($type);
                        if (stripos($charset_part, 'charset=') !== false) {
                            $result['charset'] = trim(str_replace(array('charset=', '"', "'"), '', $charset_part));
                        }
                    } else {
                        $result['content_type'] = trim($content);
                    }
                    break;

                case 'cc':
                    $cc_addresses = explode(',', $content);
                    foreach ($cc_addresses as $cc) {
                        $cc = trim($cc);
                        if (!empty($cc)) {
                            $result['cc'][] = $cc;
                        }
                    }
                    break;

                case 'bcc':
                    $bcc_addresses = explode(',', $content);
                    foreach ($bcc_addresses as $bcc) {
                        $bcc = trim($bcc);
                        if (!empty($bcc)) {
                            $result['bcc'][] = $bcc;
                        }
                    }
                    break;

                default:
                    $result['headers'][$name] = $content;
                    break;
            }
        }

        return $result;
    }

    /**
     * Build multipart form data payload from body array
     *
     * @param array $body Body data
     * @param string $boundary Boundary string
     * @return string Payload string
     */
    private static function build_payload_from_body($body, $boundary)
    {
        $payload = '';

        foreach ($body as $key => $value) {
            if (is_array($value)) {
                // Handle array values (like tags)
                foreach ($value as $item) {
                    $payload .= '--' . $boundary . "\r\n";
                    $payload .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
                    $payload .= $item . "\r\n";
                }
            } else {
                $payload .= '--' . $boundary . "\r\n";
                $payload .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
                $payload .= $value . "\r\n";
            }
        }

        return $payload;
    }

    /**
     * Build attachments payload
     *
     * @param array $attachments Attachments array
     * @param string $boundary Boundary string
     * @return string Payload string
     */
    private static function build_attachments_payload($attachments, $boundary)
    {
        $payload = '';

        if (empty($attachments)) {
            return $payload;
        }

        $i = 0;
        foreach ($attachments as $attachment) {
            if (!empty($attachment) && file_exists($attachment)) {
                $payload .= '--' . $boundary . "\r\n";
                $payload .= 'Content-Disposition: form-data; name="attachment[' . $i . ']"; filename="' . basename($attachment) . "\"\r\n\r\n";
                $payload .= file_get_contents($attachment) . "\r\n";
                $i++;
            }
        }

        return $payload;
    }

    /**
     * Send email via Mailgun API
     *
     * @param string|array $to Recipient email address(es)
     * @param string $subject Email subject
     * @param string $message Email message
     * @param string|array $headers Optional email headers
     * @param array $attachments Optional attachments
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function send_email($to, $subject, $message, $headers = array(), $attachments = array())
    {
        $config = self::get_config();
        if (!$config) {
            return new WP_Error('mailgun_not_configured', __('Mailgun is not configured or enabled.', 'pexpress'));
        }

        // Parse headers
        $parsed_headers = self::parse_headers($headers);

        // Get from name and email from config or headers
        $options = get_option('pexpress_options', array());
        $email_config = isset($options['email_config']) ? $options['email_config'] : array();

        $from_name = $parsed_headers['from_name'];
        if (empty($from_name)) {
            $from_name = isset($email_config['from_name']) ? $email_config['from_name'] : get_bloginfo('name');
        }

        $from_email = $parsed_headers['from_email'];
        if (empty($from_email)) {
            $from_email = isset($email_config['from_email']) ? $email_config['from_email'] : get_option('admin_email');
        }

        // Ensure from_email is valid
        if (!is_email($from_email)) {
            $from_email = get_option('admin_email');
        }

        $from_string = $from_name . ' <' . $from_email . '>';

        // Prepare recipients
        if (!is_array($to)) {
            $to = explode(',', $to);
        }
        $to = array_map('trim', $to);
        $to = array_filter($to, 'is_email');

        if (empty($to)) {
            return new WP_Error('no_recipients', __('No valid recipient email addresses.', 'pexpress'));
        }

        // Convert to array to comma-separated string for Mailgun API
        $to_string = implode(', ', $to);

        // Build body array
        $body = array(
            'from' => $from_string,
            'to' => $to_string,
            'subject' => $subject,
        );

        // Add CC and BCC
        if (!empty($parsed_headers['cc'])) {
            $body['cc'] = implode(', ', $parsed_headers['cc']);
        }
        if (!empty($parsed_headers['bcc'])) {
            $body['bcc'] = implode(', ', $parsed_headers['bcc']);
        }

        // Determine content type
        $content_type = $parsed_headers['content_type'];
        if (empty($content_type)) {
            // Try to detect if message is HTML
            if (strip_tags($message) !== $message) {
                $content_type = 'text/html';
            } else {
                $content_type = 'text/plain';
            }
        }

        // Set message body based on content type
        if ($content_type === 'text/html' || stripos($content_type, 'html') !== false) {
            $body['html'] = $message;
        } else {
            $body['text'] = $message;
        }

        // Add custom headers
        foreach ($parsed_headers['headers'] as $name => $content) {
            $body['h:' . $name] = $content;
        }

        // Build multipart payload
        $boundary = sha1(uniqid('', true));
        $payload = self::build_payload_from_body($body, $boundary);
        $payload .= self::build_attachments_payload($attachments, $boundary);
        $payload .= '--' . $boundary . '--';

        // Prepare request
        $endpoint = self::get_api_endpoint($config['region']);
        $url = $endpoint . $config['domain'] . '/messages';

        $request_args = array(
            'body' => $payload,
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('api:' . $config['api_key']),
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            ),
            'sslverify' => true,
            'timeout' => 30,
        );

        // Send request
        $response = wp_remote_post($url, $request_args);

        $response_code = null;
        $response_body = '';

        if (is_wp_error($response)) {
            $error_msg = __('Failed to send email via Mailgun: ', 'pexpress') . $response->get_error_message();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PExpress Mailgun Request Error: ' . $error_msg);
                error_log('PExpress Mailgun URL: ' . $url);
            }
            return new WP_Error('mailgun_request_failed', $error_msg);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PExpress Mailgun Response Code: ' . $response_code);
            error_log('PExpress Mailgun Response Body: ' . substr($response_body, 0, 500));
        }

        if ($response_code !== 200) {
            $error_message = __('Mailgun API error: ', 'pexpress') . $response_code;
            $response_data = json_decode($response_body, true);
            if (isset($response_data['message'])) {
                $error_message .= ' - ' . $response_data['message'];
            }
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PExpress Mailgun API Error: ' . $error_message);
                error_log('PExpress Mailgun Full Response: ' . print_r($response_data, true));
            }
            return new WP_Error('mailgun_api_error', $error_message, array('response_code' => $response_code, 'response_body' => $response_body));
        }

        $response_data = json_decode($response_body, true);
        if (isset($response_data['message']) && $response_data['message'] === 'Queued. Thank you.') {
            return true;
        }

        $error_msg = __('Unexpected response from Mailgun API.', 'pexpress');
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PExpress Mailgun Unexpected Response: ' . print_r($response_data, true));
        }
        return new WP_Error('mailgun_unexpected_response', $error_msg, array('response_code' => $response_code, 'response_body' => $response_body));
    }
}

