<?php

/**
 * Core plugin functionality
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core plugin class
 */
class PExpress_Core
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize core functionality
     */
    private function init()
    {
        // Initialize order statuses
        PExpress_Order_Statuses::init();
    }

    /**
     * Get order meta value
     *
     * @param int    $order_id Order ID.
     * @param string $key      Meta key.
     * @param bool   $single   Whether to return single value.
     * @return mixed
     */
    public static function get_order_meta($order_id, $key, $single = true)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        return $order->get_meta($key, $single);
    }

    /**
     * Update order meta value
     *
     * @param int    $order_id Order ID.
     * @param string $key       Meta key.
     * @param mixed  $value     Meta value.
     * @return int|bool Meta ID on success, false on failure.
     */
    public static function update_order_meta($order_id, $key, $value)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        $order->update_meta_data($key, $value);
        return $order->save();
    }

    /**
     * Get assigned delivery user ID for an order
     *
     * @param int $order_id Order ID.
     * @return int|false
     */
    public static function get_delivery_user_id($order_id)
    {
        return (int) self::get_order_meta($order_id, '_polar_delivery_user_id');
    }

    /**
     * Get assigned fridge user ID for an order
     *
     * @param int $order_id Order ID.
     * @return int|false
     */
    public static function get_fridge_user_id($order_id)
    {
        return (int) self::get_order_meta($order_id, '_polar_fridge_user_id');
    }

    /**
     * Get assigned distributor user ID for an order
     *
     * @param int $order_id Order ID.
     * @return int|false
     */
    public static function get_distributor_user_id($order_id)
    {
        return (int) self::get_order_meta($order_id, '_polar_distributor_user_id');
    }

    /**
     * Get meeting type (meet_point or delivery_location)
     *
     * @param int $order_id Order ID.
     * @return string
     */
    public static function get_meeting_type($order_id)
    {
        $meeting_type = self::get_order_meta($order_id, '_polar_meeting_type');
        return $meeting_type ?: 'meet_point';
    }

    /**
     * Get meeting location text
     *
     * @param int $order_id Order ID.
     * @return string
     */
    public static function get_meeting_location($order_id)
    {
        return (string) self::get_order_meta($order_id, '_polar_meeting_location');
    }

    /**
     * Get scheduled meeting datetime
     * Uses _polar_meeting_datetime when set (e.g. from HR dashboard). Otherwise falls back to
     * billing/checkout event date and time meta (e.g. Select Date + Select Time from checkout).
     *
     * @param int $order_id Order ID.
     * @return string
     */
    public static function get_meeting_datetime($order_id)
    {
        $datetime = (string) self::get_order_meta($order_id, '_polar_meeting_datetime');
        if ($datetime !== '') {
            return $datetime;
        }

        // Fallback: build from separate date + time meta (checkout/billing event fields).
        // WooCommerce order data meta box uses $order->get_meta('_' . 'billing_' . $key), so
        // custom fields date_ and time_ are stored as _billing_date_ and _billing_time_.
        $date_time_pairs = array(
            array('_billing_date_', '_billing_time_'),
            array('_date_', '_time_'),
            array('date_', 'time_'),
            array('_billing_event_date', '_billing_event_time'),
            array('event_date', 'event_time'),
            array('_billing_select_date', '_billing_select_time'),
            array('select_date', 'select_time'),
            array('_billing_date', '_billing_time'),
        );
        foreach ($date_time_pairs as $pair) {
            $date_val = trim((string) self::get_order_meta($order_id, $pair[0]));
            $time_val = trim((string) self::get_order_meta($order_id, $pair[1]));
            if ($date_val !== '' && $time_val !== '') {
                // Normalize time to H:i or H:i:s
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time_val)) {
                    $time_val = strlen($time_val) === 5 ? $time_val . ':00' : $time_val;
                }
                $combined = $date_val . ' ' . $time_val;
                if (strtotime($combined) !== false) {
                    return $combined;
                }
            }
        }

        return '';
    }

    /**
     * Get fridge asset identifier
     *
     * @param int $order_id Order ID.
     * @return string
     */
    public static function get_fridge_asset_id($order_id)
    {
        return (string) self::get_order_meta($order_id, '_polar_fridge_asset_id');
    }

    /**
     * Get instructions saved for a role
     *
     * @param int    $order_id Order ID.
     * @param string $role_key Role key (delivery|fridge|distributor).
     * @return string
     */
    public static function get_role_instructions($order_id, $role_key)
    {
        $meta_key = sprintf('_polar_instructions_%s', sanitize_key($role_key));
        return (string) self::get_order_meta($order_id, $meta_key);
    }

    /**
     * Get per-role status for an order
     *
     * @param int    $order_id Order ID.
     * @param string $role_key Role key (agency|delivery|fridge|distributor).
     * @return string Status value, defaults to 'pending' if not set.
     */
    public static function get_role_status($order_id, $role_key)
    {
        $meta_key = sprintf('_polar_status_%s', sanitize_key($role_key));
        $status = self::get_order_meta($order_id, $meta_key);
        return $status ?: 'pending';
    }

    /**
     * Update per-role status for an order
     *
     * @param int    $order_id Order ID.
     * @param string $role_key Role key (agency|delivery|fridge|distributor).
     * @param string $status   Status value.
     * @return bool|int Meta ID on success, false on failure.
     */
    public static function update_role_status($order_id, $role_key, $status)
    {
        $meta_key = sprintf('_polar_status_%s', sanitize_key($role_key));
        return self::update_order_meta($order_id, $meta_key, sanitize_text_field($status));
    }

    /**
     * Get all role statuses for an order
     *
     * @param int $order_id Order ID.
     * @return array Associative array of role_key => status.
     */
    public static function get_all_role_statuses($order_id)
    {
        $roles = array('agency', 'delivery', 'fridge', 'distributor');
        $statuses = array();
        foreach ($roles as $role) {
            $statuses[$role] = self::get_role_status($order_id, $role);
        }
        return $statuses;
    }

    /**
     * Get role status history for an order
     *
     * @param int    $order_id Order ID.
     * @param string $role_key Role key (agency|delivery|fridge|distributor).
     * @return array Array of status history entries.
     */
    public static function get_role_status_history($order_id, $role_key)
    {
        $meta_key = sprintf('_polar_status_history_%s', sanitize_key($role_key));
        $history = self::get_order_meta($order_id, $meta_key, true);
        if (!is_array($history)) {
            $history = array();
        }
        return $history;
    }

    /**
     * Add entry to role status history
     *
     * @param int    $order_id Order ID.
     * @param string $role_key Role key (agency|delivery|fridge|distributor).
     * @param string $status   Status value.
     * @param string $note     Optional note.
     * @param int    $user_id  Optional user ID (defaults to current user).
     * @return bool|int Meta ID on success, false on failure.
     */
    public static function add_role_status_history($order_id, $role_key, $status, $note = '', $user_id = 0)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        $user = get_userdata($user_id);
        $user_name = $user ? $user->display_name : __('System', 'pexpress');

        $history = self::get_role_status_history($order_id, $role_key);
        $history[] = array(
            'status' => sanitize_text_field($status),
            'note' => sanitize_textarea_field($note),
            'user_id' => absint($user_id),
            'user_name' => sanitize_text_field($user_name),
            'timestamp' => current_time('mysql'),
        );

        $meta_key = sprintf('_polar_status_history_%s', sanitize_key($role_key));
        return self::update_order_meta($order_id, $meta_key, $history);
    }

    /**
     * Get stage-wise order tracking data for an order (current stage, responsible person, contact, last update).
     *
     * @param int $order_id Order ID.
     * @return array List of stages with stage_key, stage_label, current_status, status_label, responsible_user_id, responsible_name, contact_phone, last_update_timestamp, last_update_note.
     */
    public static function get_stage_wise_tracking($order_id)
    {
        $order_id = absint($order_id);
        if (!$order_id) {
            return array();
        }

        $status_labels = array(
            'agency' => array(
                'pending' => __('Pending', 'pexpress'),
                'assigned' => __('Assigned', 'pexpress'),
                'proceeded' => __('Proceeded', 'pexpress'),
                'confirmed' => __('Confirmed', 'pexpress'),
                'completed' => __('Completed', 'pexpress'),
            ),
            'delivery' => array(
                'pending' => __('Pending', 'pexpress'),
                'meet_point_arrived' => __('Reached Meet Point', 'pexpress'),
                'delivery_location_arrived' => __('Reached Delivery Location', 'pexpress'),
                'service_in_progress' => __('Service In Progress', 'pexpress'),
                'service_complete' => __('Service Completed', 'pexpress'),
                'customer_served' => __('Ice-cream Delivered', 'pexpress'),
            ),
            'fridge' => array(
                'pending' => __('Pending', 'pexpress'),
                'fridge_drop' => __('Fridge Delivered On-site', 'pexpress'),
                'fridge_collected' => __('Fridge Collected On-site', 'pexpress'),
                'fridge_returned' => __('Fridge Returned to Base', 'pexpress'),
            ),
            'distributor' => array(
                'pending' => __('Pending', 'pexpress'),
                'distributor_prep' => __('Product Provider Preparing', 'pexpress'),
                'out_for_delivery' => __('Out for Delivery', 'pexpress'),
                'handoff_complete' => __('Product Provider Handoff Complete', 'pexpress'),
            ),
        );

        $stage_config = array(
            array('key' => 'agency', 'label' => __('Distribution', 'pexpress'), 'user_id_key' => null),
            array('key' => 'delivery', 'label' => __('SR', 'pexpress'), 'user_id_key' => 'delivery'),
            array('key' => 'fridge', 'label' => __('Fridge Dept (FSD)', 'pexpress'), 'user_id_key' => 'fridge'),
            array('key' => 'distributor', 'label' => __('Product Provider', 'pexpress'), 'user_id_key' => 'distributor'),
        );

        $stages = array();
        foreach ($stage_config as $config) {
            $role_key = $config['key'];
            $current_status = self::get_role_status($order_id, $role_key);
            if ($current_status === '' || $current_status === null) {
                $current_status = 'pending';
            }
            $labels = isset($status_labels[$role_key]) ? $status_labels[$role_key] : array();
            $status_label = isset($labels[$current_status]) ? $labels[$current_status] : ucfirst(str_replace('_', ' ', $current_status));

            $responsible_user_id = 0;
            if ($config['user_id_key'] === 'delivery') {
                $responsible_user_id = absint(self::get_delivery_user_id($order_id));
            } elseif ($config['user_id_key'] === 'fridge') {
                $responsible_user_id = absint(self::get_fridge_user_id($order_id));
            } elseif ($config['user_id_key'] === 'distributor') {
                $responsible_user_id = absint(self::get_distributor_user_id($order_id));
            }

            $responsible_name = '';
            $contact_phone = '';
            if ($responsible_user_id) {
                $user = get_userdata($responsible_user_id);
                $responsible_name = $user ? $user->display_name : '';
                if ($responsible_name === '' && $user) {
                    $responsible_name = $user->user_login;
                }
                if ($responsible_name === '') {
                    $responsible_name = sprintf(__('User #%d', 'pexpress'), $responsible_user_id);
                }
                $phone_keys = array('billing_phone', 'phone', 'mobile', 'user_phone', 'telephone');
                foreach ($phone_keys as $meta_key) {
                    $contact_phone = (string) get_user_meta($responsible_user_id, $meta_key, true);
                    if ($contact_phone !== '') {
                        break;
                    }
                }
            }

            $history = self::get_role_status_history($order_id, $role_key);
            $last_update_timestamp = '';
            $last_update_formatted = '';
            $last_update_note = '';
            if (!empty($history) && is_array($history)) {
                $last = end($history);
                $last_update_timestamp = isset($last['timestamp']) ? $last['timestamp'] : '';
                $last_update_formatted = $last_update_timestamp ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $last_update_timestamp) : '';
                $last_update_note = isset($last['note']) ? $last['note'] : '';
            }

            $stages[] = array(
                'stage_key' => $role_key,
                'stage_label' => $config['label'],
                'current_status' => $current_status,
                'status_label' => $status_label,
                'responsible_user_id' => $responsible_user_id,
                'responsible_name' => $responsible_name,
                'contact_phone' => $contact_phone,
                'last_update_timestamp' => $last_update_timestamp,
                'last_update_formatted' => $last_update_formatted,
                'last_update_note' => $last_update_note,
            );
        }

        return $stages;
    }

    /**
     * Get orders assigned to a user
     *
     * @param int    $user_id User ID.
     * @param string $role    Role type (delivery, fridge, distributor).
     * @return array
     */
    public static function get_assigned_orders($user_id, $role = 'delivery')
    {
        $meta_key = '_polar_' . sanitize_key($role) . '_user_id';
        $user_id = absint($user_id);
        $current_user = wp_get_current_user();
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        // Debug logging
        if ($debug_enabled) {
            error_log(sprintf(
                '[PEXPRESS DEBUG] get_assigned_orders called - User ID: %d, Role: %s, Meta Key: %s, Current User ID: %d, Current User Roles: %s',
                $user_id,
                $role,
                $meta_key,
                get_current_user_id(),
                implode(', ', $current_user->roles)
            ));
        }

        if (!$user_id) {
            if ($debug_enabled) {
                error_log('[PEXPRESS DEBUG] get_assigned_orders - Invalid user ID, returning empty array');
            }
            return array();
        }

        // Check if WooCommerce is using HPOS (High-Performance Order Storage)
        $is_hpos = false;
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            $is_hpos = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] HPOS enabled: %s', $is_hpos ? 'YES' : 'NO'));
        }

        if ($is_hpos) {
            // For HPOS, WooCommerce uses a different meta query structure
            // Try multiple approaches for HPOS compatibility
            $args = array(
                'status' => 'any',
                'limit'  => -1,
                'orderby' => 'id',
                'order'   => 'DESC',
                'meta_query' => array(
                    array(
                        'key' => $meta_key,
                        'value' => $user_id,
                        'compare' => '=',
                    ),
                ),
            );
        } else {
            // For CPT, use traditional meta_key/meta_value for better compatibility
            $args = array(
                'status' => 'any',
                'limit'  => -1,
                'orderby' => 'id',
                'order'   => 'DESC',
                'meta_key' => $meta_key,
                'meta_value' => $user_id,
            );
        }

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] Query args (primary): %s', print_r($args, true)));
        }

        $orders = wc_get_orders($args);
        $order_count = is_array($orders) ? count($orders) : 0;

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] Primary query returned %d orders', $order_count));
            if ($order_count > 0) {
                $order_ids = array_map(function ($order) {
                    return $order->get_id();
                }, $orders);
                error_log(sprintf('[PEXPRESS DEBUG] Order IDs found: %s', implode(', ', $order_ids)));
            }
        }

        // Fallback: If no orders found with primary method, try alternative
        if (empty($orders) || !is_array($orders)) {
            if ($debug_enabled) {
                error_log('[PEXPRESS DEBUG] Primary query returned no results, trying fallback query');
            }

            // For HPOS, try direct database query and load orders
            if ($is_hpos) {
                global $wpdb;
                $meta_table = $wpdb->prefix . 'wc_orders_meta';

                // Try integer value
                $order_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT order_id FROM {$meta_table} WHERE meta_key = %s AND meta_value = %d",
                    $meta_key,
                    $user_id
                ));

                // If no results, try string value
                if (empty($order_ids)) {
                    $order_ids = $wpdb->get_col($wpdb->prepare(
                        "SELECT order_id FROM {$meta_table} WHERE meta_key = %s AND meta_value = %s",
                        $meta_key,
                        (string) $user_id
                    ));
                }

                if (!empty($order_ids)) {
                    $orders = array();
                    foreach ($order_ids as $order_id) {
                        $order = wc_get_order($order_id);
                        if ($order) {
                            $orders[] = $order;
                        }
                    }
                    if ($debug_enabled) {
                        error_log(sprintf('[PEXPRESS DEBUG] HPOS direct DB query found %d orders: %s', count($orders), implode(', ', $order_ids)));
                    }
                }
            }

            // If still no orders, try with meta_query regardless of storage type
            if (empty($orders) || !is_array($orders)) {
                $fallback_args = array(
                    'status' => 'any',
                    'limit'  => -1,
                    'orderby' => 'id',
                    'order'   => 'DESC',
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => $meta_key,
                            'value' => $user_id,
                            'compare' => '=',
                            'type' => 'NUMERIC',
                        ),
                        array(
                            'key' => $meta_key,
                            'value' => (string) $user_id,
                            'compare' => '=',
                        ),
                    ),
                );

                if ($debug_enabled) {
                    error_log(sprintf('[PEXPRESS DEBUG] Fallback query args: %s', print_r($fallback_args, true)));
                }

                $orders = wc_get_orders($fallback_args);
                $fallback_count = is_array($orders) ? count($orders) : 0;

                if ($debug_enabled) {
                    error_log(sprintf('[PEXPRESS DEBUG] Fallback query returned %d orders', $fallback_count));
                    if ($fallback_count > 0) {
                        $order_ids = array_map(function ($order) {
                            return $order->get_id();
                        }, $orders);
                        error_log(sprintf('[PEXPRESS DEBUG] Fallback Order IDs found: %s', implode(', ', $order_ids)));
                    }
                }
            }

            // If still no orders, check database directly
            if (empty($orders) && $debug_enabled) {
                global $wpdb;
                $order_meta_table = $is_hpos ? $wpdb->prefix . 'wc_orders_meta' : $wpdb->postmeta;
                $order_id_column = $is_hpos ? 'order_id' : 'post_id';

                $query = $wpdb->prepare(
                    "SELECT {$order_id_column} FROM {$order_meta_table} WHERE meta_key = %s AND meta_value = %s",
                    $meta_key,
                    $user_id
                );
                $direct_results = $wpdb->get_col($query);

                error_log(sprintf(
                    '[PEXPRESS DEBUG] Direct database query (table: %s) found %d results: %s',
                    $order_meta_table,
                    count($direct_results),
                    implode(', ', $direct_results)
                ));

                // Also check for string value
                $query_string = $wpdb->prepare(
                    "SELECT {$order_id_column} FROM {$order_meta_table} WHERE meta_key = %s AND meta_value = %s",
                    $meta_key,
                    (string) $user_id
                );
                $direct_results_string = $wpdb->get_col($query_string);

                error_log(sprintf(
                    '[PEXPRESS DEBUG] Direct database query (string value) found %d results: %s',
                    count($direct_results_string),
                    implode(', ', $direct_results_string)
                ));

                // Check all meta values for this key
                $all_meta_query = $wpdb->prepare(
                    "SELECT {$order_id_column}, meta_value FROM {$order_meta_table} WHERE meta_key = %s LIMIT 20",
                    $meta_key
                );
                $all_meta = $wpdb->get_results($all_meta_query);

                // Store database query results for console output
                if (!isset($GLOBALS['pexpress_debug_db_results'])) {
                    $GLOBALS['pexpress_debug_db_results'] = array();
                }
                $GLOBALS['pexpress_debug_db_results'][] = array(
                    'meta_key' => $meta_key,
                    'user_id' => $user_id,
                    'table' => $order_meta_table,
                    'direct_results_int' => $direct_results,
                    'direct_results_string' => $direct_results_string,
                    'all_meta_samples' => $all_meta ? array_slice($all_meta, 0, 5) : array(),
                );

                if ($all_meta) {
                    $meta_values = array();
                    foreach ($all_meta as $meta) {
                        $meta_values[] = sprintf(
                            'Order %s: value=%s (type: %s)',
                            $meta->{$order_id_column},
                            $meta->meta_value,
                            gettype($meta->meta_value)
                        );
                    }
                    error_log(sprintf('[PEXPRESS DEBUG] Sample meta values for key %s: %s', $meta_key, implode(' | ', $meta_values)));
                }
            }
        }

        // Ensure we return an array
        if (!is_array($orders)) {
            if ($debug_enabled) {
                error_log(sprintf('[PEXPRESS DEBUG] Orders is not an array, type: %s', gettype($orders)));
            }
            return array();
        }

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] get_assigned_orders returning %d orders', count($orders)));

            // Also output to browser console via JavaScript
            $debug_data = array(
                'user_id' => $user_id,
                'role' => $role,
                'meta_key' => $meta_key,
                'is_hpos' => $is_hpos,
                'order_count' => count($orders),
                'order_ids' => array_map(function ($order) {
                    return $order->get_id();
                }, $orders),
            );

            // Store debug data for JavaScript output
            if (!isset($GLOBALS['pexpress_debug_data'])) {
                $GLOBALS['pexpress_debug_data'] = array();
            }
            $GLOBALS['pexpress_debug_data'][] = $debug_data;
        }

        return $orders;
    }

    /**
     * Get all orders that have at least one role assignment (delivery, fridge, or distributor).
     * Used by Agency dashboard so "In Progress" shows the same orders that appear on role dashboards.
     *
     * @param int $limit Optional. Max orders to return. Default 200.
     * @return array Array of WC_Order objects.
     */
    public static function get_orders_with_any_assignment($limit = 200)
    {
        // Query orders that have any of the three assignment keys set (EXISTS avoids numeric/string issues in HPOS).
        $args = array(
            'status'     => 'any',
            'limit'     => $limit * 2,
            'orderby'   => 'id',
            'order'     => 'DESC',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => '_polar_delivery_user_id',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'     => '_polar_fridge_user_id',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'     => '_polar_distributor_user_id',
                    'compare' => 'EXISTS',
                ),
            ),
        );

        $orders = wc_get_orders($args);
        if (!is_array($orders)) {
            return array();
        }

        // Keep only orders where at least one assignment is non-zero (key can exist with empty value).
        $filtered = array();
        foreach ($orders as $order) {
            if (!$order || !is_a($order, 'WC_Order')) {
                continue;
            }
            $oid = $order->get_id();
            $d = (int) self::get_order_meta($oid, '_polar_delivery_user_id');
            $f = (int) self::get_order_meta($oid, '_polar_fridge_user_id');
            $x = (int) self::get_order_meta($oid, '_polar_distributor_user_id');
            if ($d > 0 || $f > 0 || $x > 0) {
                $filtered[] = $order;
                if (count($filtered) >= $limit) {
                    break;
                }
            }
        }
        return $filtered;
    }

    /**
     * Output debug data as JavaScript console logs
     *
     * @param bool $return_string Whether to return string instead of outputting directly.
     * @return string|void
     */
    public static function output_debug_console($return_string = false)
    {
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        if (!$debug_enabled) {
            return $return_string ? '' : null;
        }

        if (!isset($GLOBALS['pexpress_debug_data']) || empty($GLOBALS['pexpress_debug_data'])) {
            return $return_string ? '' : null;
        }

        $debug_data = $GLOBALS['pexpress_debug_data'];
        $output = '<script type="text/javascript">' . "\n";
        $output .= "console.group('🔍 PEXPRESS DEBUG - Order Query Results');\n";

        foreach ($debug_data as $index => $data) {
            $output .= sprintf(
                "console.log('Query #%d:', {\n",
                $index + 1
            );
            $output .= sprintf("  user_id: %d,\n", $data['user_id']);
            $output .= sprintf("  role: '%s',\n", esc_js($data['role']));
            $output .= sprintf("  meta_key: '%s',\n", esc_js($data['meta_key']));
            $output .= sprintf("  is_hpos: %s,\n", $data['is_hpos'] ? 'true' : 'false');
            $output .= sprintf("  order_count: %d,\n", $data['order_count']);
            $output .= sprintf("  order_ids: %s\n", json_encode($data['order_ids']));
            $output .= "});\n";
        }

        // Add database query results if available
        if (isset($GLOBALS['pexpress_debug_db_results']) && !empty($GLOBALS['pexpress_debug_db_results'])) {
            $output .= "console.group('📊 Direct Database Query Results');\n";
            foreach ($GLOBALS['pexpress_debug_db_results'] as $index => $db_data) {
                $output .= sprintf("console.log('DB Query #%d:', {\n", $index + 1);
                $output .= sprintf("  meta_key: '%s',\n", esc_js($db_data['meta_key']));
                $output .= sprintf("  user_id: %d,\n", $db_data['user_id']);
                $output .= sprintf("  table: '%s',\n", esc_js($db_data['table']));
                $output .= sprintf("  direct_results_int: %s,\n", json_encode($db_data['direct_results_int']));
                $output .= sprintf("  direct_results_string: %s,\n", json_encode($db_data['direct_results_string']));
                if (!empty($db_data['all_meta_samples'])) {
                    $samples = array();
                    foreach ($db_data['all_meta_samples'] as $sample) {
                        $order_id_col = strpos($db_data['table'], 'wc_orders_meta') !== false ? 'order_id' : 'post_id';
                        $samples[] = sprintf('Order %s: value=%s', $sample->{$order_id_col}, $sample->meta_value);
                    }
                    $output .= sprintf("  sample_meta_values: %s,\n", json_encode($samples));
                }
                $output .= "});\n";
            }
            $output .= "console.groupEnd();\n";
            unset($GLOBALS['pexpress_debug_db_results']);
        }

        $output .= "console.groupEnd();\n";
        $output .= '</script>' . "\n";

        // Clear debug data after output
        unset($GLOBALS['pexpress_debug_data']);

        if ($return_string) {
            return $output;
        }

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Check if order needs assignment
     *
     * @param int $order_id Order ID.
     * @return bool
     */
    public static function order_needs_assignment($order_id)
    {
        return 'yes' === self::get_order_meta($order_id, '_polar_needs_assignment');
    }

    /**
     * Get billing name for an order
     *
     * @param WC_Order|int $order Order object or order ID.
     * @return string
     */
    public static function get_billing_name($order)
    {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }

        if (!$order || !is_a($order, 'WC_Order')) {
            return '';
        }

        $first_name = $order->get_billing_first_name() ?: '';
        $last_name = $order->get_billing_last_name() ?: '';

        $name = trim($first_name . ' ' . $last_name);

        // Fallback to customer name if billing name is empty
        if (empty($name)) {
            $customer_id = $order->get_customer_id();
            if ($customer_id) {
                $customer = new WC_Customer($customer_id);
                $display_name = $customer->get_display_name();
                $name = $display_name ?: '';
            }
        }

        // Final fallback
        if (empty($name)) {
            $company = $order->get_billing_company();
            $name = $company ?: __('Guest', 'pexpress');
        }

        return $name ?: __('Guest', 'pexpress');
    }
}
