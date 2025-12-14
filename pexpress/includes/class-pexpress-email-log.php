<?php

/**
 * Email Log System
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email log handler class
 */
class PExpress_Email_Log
{
    /**
     * Get table name
     *
     * @return string
     */
    private static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'pexpress_email_log';
    }

    /**
     * Create email log table
     */
    public static function create_table()
    {
        global $wpdb;
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            to_email varchar(255) NOT NULL,
            subject varchar(500) NOT NULL,
            message text,
            headers text,
            method varchar(50) DEFAULT 'wp_mail',
            status varchar(50) DEFAULT 'pending',
            error_message text,
            response_code int(11) DEFAULT NULL,
            response_body text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY to_email (to_email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Log email attempt
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email message
     * @param array $headers Email headers
     * @param string $method Sending method (mailgun, wp_mail)
     * @param string $status Status (pending, success, failed)
     * @param string $error_message Error message if failed
     * @param int $response_code HTTP response code
     * @param string $response_body Response body
     * @return int|false Log ID or false on failure
     */
    public static function log($to, $subject, $message, $headers = array(), $method = 'wp_mail', $status = 'pending', $error_message = '', $response_code = null, $response_body = '')
    {
        global $wpdb;
        $table_name = self::get_table_name();

        // Truncate message if too long
        $message_truncated = strlen($message) > 10000 ? substr($message, 0, 10000) . '...' : $message;
        $headers_serialized = is_array($headers) ? serialize($headers) : $headers;
        $headers_serialized = strlen($headers_serialized) > 5000 ? substr($headers_serialized, 0, 5000) . '...' : $headers_serialized;

        $result = $wpdb->insert(
            $table_name,
            array(
                'to_email' => sanitize_email($to),
                'subject' => sanitize_text_field($subject),
                'message' => $message_truncated,
                'headers' => $headers_serialized,
                'method' => sanitize_text_field($method),
                'status' => sanitize_text_field($status),
                'error_message' => sanitize_textarea_field($error_message),
                'response_code' => $response_code,
                'response_body' => substr($response_body, 0, 5000),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update log entry
     *
     * @param int $log_id Log ID
     * @param string $status Status
     * @param string $error_message Error message
     * @param int $response_code Response code
     * @param string $response_body Response body
     * @param string $method Method (mailgun, wp_mail)
     * @return bool
     */
    public static function update_log($log_id, $status, $error_message = '', $response_code = null, $response_body = '', $method = '')
    {
        global $wpdb;
        $table_name = self::get_table_name();

        $data = array(
            'status' => sanitize_text_field($status),
        );

        $format = array('%s');

        if (!empty($error_message)) {
            $data['error_message'] = sanitize_textarea_field($error_message);
            $format[] = '%s';
        }

        if ($response_code !== null) {
            $data['response_code'] = intval($response_code);
            $format[] = '%d';
        }

        if (!empty($response_body)) {
            $data['response_body'] = substr($response_body, 0, 5000);
            $format[] = '%s';
        }

        if (!empty($method)) {
            $data['method'] = sanitize_text_field($method);
            $format[] = '%s';
        }

        return $wpdb->update(
            $table_name,
            $data,
            array('id' => intval($log_id)),
            $format,
            array('%d')
        ) !== false;
    }

    /**
     * Get logs
     *
     * @param int $limit Number of logs to retrieve
     * @param int $offset Offset
     * @param string $status Filter by status
     * @return array
     */
    public static function get_logs($limit = 50, $offset = 0, $status = '')
    {
        global $wpdb;
        $table_name = self::get_table_name();

        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare('WHERE status = %s', $status);
        }

        $query = "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query = $wpdb->prepare($query, $limit, $offset);

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get log count
     *
     * @param string $status Filter by status
     * @return int
     */
    public static function get_log_count($status = '')
    {
        global $wpdb;
        $table_name = self::get_table_name();

        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare('WHERE status = %s', $status);
        }

        $query = "SELECT COUNT(*) FROM $table_name $where";
        return (int) $wpdb->get_var($query);
    }

    /**
     * Clear old logs
     *
     * @param int $days Number of days to keep
     * @return int Number of deleted rows
     */
    public static function clear_old_logs($days = 30)
    {
        global $wpdb;
        $table_name = self::get_table_name();

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE created_at < %s", $date));
    }

    /**
     * Delete log entry
     *
     * @param int $log_id Log ID
     * @return bool
     */
    public static function delete_log($log_id)
    {
        global $wpdb;
        $table_name = self::get_table_name();

        return $wpdb->delete($table_name, array('id' => intval($log_id)), array('%d')) !== false;
    }
}

