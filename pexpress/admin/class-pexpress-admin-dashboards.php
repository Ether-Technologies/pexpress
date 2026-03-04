<?php

/**
 * Admin dashboard rendering
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin dashboards handler
 */
class PExpress_Admin_Dashboards
{

    /**
     * Render Agent Dashboard page (formerly HR)
     */
    public function render_agency_dashboard()
    {
        $current_user = wp_get_current_user();
        if (!in_array('polar_hr', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        // Get orders needing assignment
        $pending_orders = wc_get_orders(array(
            'status' => 'processing',
            'limit' => -1,
            'meta_query' => array(
                array(
                    'key' => '_polar_needs_assignment',
                    'value' => 'yes',
                    'compare' => '=',
                ),
            ),
        ));

        // Get all HR (formerly delivery), fridge, and distributor users (needed for fallback and template)
        $hr_users = get_users(array('role' => 'polar_delivery'));
        $fridge_users = get_users(array('role' => 'polar_fridge'));
        $distributor_users = get_users(array('role' => 'polar_distributor'));

        // Get orders already assigned but not yet completed (In Progress tab).
        // Use orders that have at least one role assignment (same source as distributor/delivery/fridge dashboards)
        // so every order visible on those dashboards also appears here.
        $orders_with_assignment = PExpress_Core::get_orders_with_any_assignment(300);
        if (empty($orders_with_assignment)) {
            // Fallback: merge assigned orders from every role user (same as each role dashboard uses).
            $seen_ids = array();
            foreach ($hr_users as $u) {
                foreach (PExpress_Core::get_assigned_orders($u->ID, 'delivery') as $o) {
                    if ($o && is_a($o, 'WC_Order') && !isset($seen_ids[$o->get_id()])) {
                        $seen_ids[$o->get_id()] = true;
                        $orders_with_assignment[] = $o;
                    }
                }
            }
            foreach ($fridge_users as $u) {
                foreach (PExpress_Core::get_assigned_orders($u->ID, 'fridge') as $o) {
                    if ($o && is_a($o, 'WC_Order') && !isset($seen_ids[$o->get_id()])) {
                        $seen_ids[$o->get_id()] = true;
                        $orders_with_assignment[] = $o;
                    }
                }
            }
            foreach ($distributor_users as $u) {
                foreach (PExpress_Core::get_assigned_orders($u->ID, 'distributor') as $o) {
                    if ($o && is_a($o, 'WC_Order') && !isset($seen_ids[$o->get_id()])) {
                        $seen_ids[$o->get_id()] = true;
                        $orders_with_assignment[] = $o;
                    }
                }
            }
            usort($orders_with_assignment, function ($a, $b) {
                $tA = $a->get_date_created() ? $a->get_date_created()->getTimestamp() : 0;
                $tB = $b->get_date_created() ? $b->get_date_created()->getTimestamp() : 0;
                return $tB - $tA;
            });
            $orders_with_assignment = array_slice($orders_with_assignment, 0, 300);
        }

        $terminal_statuses = array('completed', 'cancelled', 'refunded', 'failed', 'checkout-draft', 'polar-complete', 'polar-delivered', 'polar-fridge-returned');
        $terminal_statuses_wc = array_map(function ($s) {
            return (strpos($s, 'wc-') === 0) ? $s : 'wc-' . $s;
        }, $terminal_statuses);

        $assigned_in_progress_orders = array();
        foreach ($orders_with_assignment as $order) {
            if (!$order || !is_a($order, 'WC_Order')) {
                continue;
            }
            $status = $order->get_status();
            $normalized = (strpos($status, 'wc-') === 0) ? $status : 'wc-' . $status;
            if (in_array($status, $terminal_statuses, true) || in_array($normalized, $terminal_statuses_wc, true)) {
                continue;
            }
            $needs = PExpress_Core::get_order_meta($order->get_id(), '_polar_needs_assignment');
            if ($needs === 'yes') {
                continue;
            }
            $assigned_in_progress_orders[] = $order;
        }
        $assigned_in_progress_orders = array_slice($assigned_in_progress_orders, 0, 100);

        // Get completed orders (exclude cancelled)
        $completed_orders = wc_get_orders(array(
            'status' => array('completed', 'wc-polar-complete', 'wc-polar-delivered', 'wc-polar-fridge-returned', 'wc-polar-service-complete', 'wc-polar-customer-served'),
            'limit' => 30,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        include PEXPRESS_PLUGIN_DIR . 'templates/hr-dashboard.php';
    }

    /**
     * Render Distribution Dashboard page (formerly Delivery)
     */
    public function render_hr_dashboard()
    {
        $current_user = wp_get_current_user();
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        if ($debug_enabled) {
            error_log(sprintf(
                '[PEXPRESS DEBUG] render_hr_dashboard - User ID: %d, Roles: %s, Has manage_woocommerce: %s',
                get_current_user_id(),
                implode(', ', $current_user->roles),
                current_user_can('manage_woocommerce') ? 'YES' : 'NO'
            ));
        }

        if (!in_array('polar_delivery', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $user_id = get_current_user_id();

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_hr_dashboard - Fetching orders for user ID: %d, role: delivery', $user_id));
        }

        // Get orders assigned to this HR person
        $assigned_orders = PExpress_Core::get_assigned_orders($user_id, 'delivery');

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_hr_dashboard - Received %d assigned orders', is_array($assigned_orders) ? count($assigned_orders) : 0));
        }

        // Output debug console logs
        PExpress_Core::output_debug_console();

        // Get orders by status
        $out_orders = array();
        $delivered_orders = array();

        foreach ($assigned_orders as $order) {
            if ($order->get_status() === 'wc-polar-out') {
                $out_orders[] = $order;
            } elseif ($order->get_status() === 'wc-polar-delivered') {
                $delivered_orders[] = $order;
            }
        }

        include PEXPRESS_PLUGIN_DIR . 'templates/delivery-dashboard.php';
    }

    /**
     * Render Fridge (FSD) Dashboard page
     */
    public function render_fridge_dashboard()
    {
        $current_user = wp_get_current_user();
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        if ($debug_enabled) {
            error_log(sprintf(
                '[PEXPRESS DEBUG] render_fridge_dashboard - User ID: %d, Roles: %s, Has manage_woocommerce: %s',
                get_current_user_id(),
                implode(', ', $current_user->roles),
                current_user_can('manage_woocommerce') ? 'YES' : 'NO'
            ));
        }

        if (!in_array('polar_fridge', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $user_id = get_current_user_id();

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_fridge_dashboard - Fetching orders for user ID: %d, role: fridge', $user_id));
        }

        // Get orders assigned to this fridge provider
        $assigned_orders = PExpress_Core::get_assigned_orders($user_id, 'fridge');

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_fridge_dashboard - Received %d assigned orders', is_array($assigned_orders) ? count($assigned_orders) : 0));
        }

        // Output debug console logs
        PExpress_Core::output_debug_console();

        // Get orders by status
        $collected_orders = array();
        $return_pending = array();

        foreach ($assigned_orders as $order) {
            $return_date = PExpress_Core::get_order_meta($order->get_id(), '_polar_fridge_return_date');
            if ($order->get_status() === 'wc-polar-fridge-back') {
                $collected_orders[] = $order;
            } else {
                $return_pending[] = $order;
            }
        }

        include PEXPRESS_PLUGIN_DIR . 'templates/fridge-dashboard.php';
    }

    /**
     * Render Distributor Dashboard page
     */
    public function render_distributor_dashboard()
    {
        $current_user = wp_get_current_user();
        // Enable debugging if WP_DEBUG is on OR if PEXPRESS_DEBUG is defined
        $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG) || (defined('PEXPRESS_DEBUG') && PEXPRESS_DEBUG);

        if ($debug_enabled) {
            error_log(sprintf(
                '[PEXPRESS DEBUG] render_distributor_dashboard - User ID: %d, Roles: %s, Has manage_woocommerce: %s',
                get_current_user_id(),
                implode(', ', $current_user->roles),
                current_user_can('manage_woocommerce') ? 'YES' : 'NO'
            ));
        }

        if (!in_array('polar_distributor', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $user_id = get_current_user_id();

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_distributor_dashboard - Fetching orders for user ID: %d, role: distributor', $user_id));
        }

        // Get orders assigned to this distributor
        $assigned_orders = PExpress_Core::get_assigned_orders($user_id, 'distributor');

        if ($debug_enabled) {
            error_log(sprintf('[PEXPRESS DEBUG] render_distributor_dashboard - Received %d assigned orders', is_array($assigned_orders) ? count($assigned_orders) : 0));
        }

        // Output debug console logs
        PExpress_Core::output_debug_console();

        include PEXPRESS_PLUGIN_DIR . 'templates/distributor-dashboard.php';
    }

    /**
     * Render Support Dashboard page
     */
    public function render_support_dashboard()
    {
        $current_user = wp_get_current_user();
        if (!in_array('polar_support', $current_user->roles) && !current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        // Fetch all recent orders (same as before)
        $recent_orders = wc_get_orders(array(
            'status' => 'any',
            'limit' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        if (!is_array($recent_orders)) {
            $recent_orders = array();
        }

        // Split into tabbed groups like Agent Dashboard
        $pending_orders = array();
        $in_progress_orders = array();
        $completed_orders = array();
        $terminal_statuses = array('completed', 'cancelled', 'refunded', 'failed', 'checkout-draft', 'polar-complete', 'polar-delivered', 'polar-fridge-returned');
        $terminal_statuses_wc = array_map(function ($s) {
            return (strpos($s, 'wc-') === 0) ? $s : 'wc-' . $s;
        }, $terminal_statuses);

        foreach ($recent_orders as $order) {
            if (!$order || !is_a($order, 'WC_Order')) {
                continue;
            }
            $order_id = $order->get_id();
            $status = $order->get_status();
            $normalized = (strpos($status, 'wc-') === 0) ? $status : 'wc-' . $status;
            $needs_assignment = PExpress_Core::get_order_meta($order_id, '_polar_needs_assignment');

            if ($needs_assignment === 'yes') {
                $pending_orders[] = $order;
            } elseif (in_array($status, $terminal_statuses, true) || in_array($normalized, $terminal_statuses_wc, true)) {
                $completed_orders[] = $order;
            } else {
                $in_progress_orders[] = $order;
            }
        }

        $recent_orders = $recent_orders; // keep for backward compatibility / "All" if needed
        include PEXPRESS_PLUGIN_DIR . 'templates/support-dashboard.php';
    }
}
