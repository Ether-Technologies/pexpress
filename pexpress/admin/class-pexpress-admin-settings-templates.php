<?php

/**
 * Templates Settings Module
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Templates Settings handler
 */
class PExpress_Admin_Settings_Templates
{
    /**
     * Register template settings
     */
    public function register_settings()
    {
        // Email Templates Section
        add_settings_section(
            'pexpress_email_templates_section',
            __('Email Template Configuration', 'pexpress'),
            array($this, 'email_templates_section_callback'),
            'polar-express-settings'
        );

        // Order Confirmed Email Template
        add_settings_field(
            'pexpress_email_order_confirmed_enable',
            __('Order Confirmed Email Alert', 'pexpress'),
            array($this, 'render_email_template_enable_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'order_confirmed')
        );

        add_settings_field(
            'pexpress_email_order_confirmed_template',
            __('Order Confirmed Email Template', 'pexpress'),
            array($this, 'render_email_template_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'order_confirmed')
        );

        // Order Proceeded Email Template
        add_settings_field(
            'pexpress_email_order_proceeded_enable',
            __('Order Proceeded Email Alert', 'pexpress'),
            array($this, 'render_email_template_enable_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'order_proceeded')
        );

        add_settings_field(
            'pexpress_email_order_proceeded_template',
            __('Order Proceeded Email Template', 'pexpress'),
            array($this, 'render_email_template_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'order_proceeded')
        );

        // Out for Delivery Email Template
        add_settings_field(
            'pexpress_email_out_for_delivery_enable',
            __('Out for Delivery Email Alert', 'pexpress'),
            array($this, 'render_email_template_enable_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'out_for_delivery')
        );

        add_settings_field(
            'pexpress_email_out_for_delivery_template',
            __('Out for Delivery Email Template', 'pexpress'),
            array($this, 'render_email_template_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'out_for_delivery')
        );

        // Order Completed Email Template
        add_settings_field(
            'pexpress_email_order_completed_enable',
            __('Order Completed Email Alert', 'pexpress'),
            array($this, 'render_email_template_enable_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'order_completed')
        );

        add_settings_field(
            'pexpress_email_order_completed_template',
            __('Order Completed Email Template', 'pexpress'),
            array($this, 'render_email_template_field'),
            'polar-express-settings',
            'pexpress_email_templates_section',
            array('template_key' => 'order_completed')
        );

        // SMS Templates Section
        add_settings_section(
            'pexpress_sms_templates_section',
            __('SMS Template Configuration', 'pexpress'),
            array($this, 'sms_templates_section_callback'),
            'polar-express-settings'
        );

        // Order Confirmed Template
        add_settings_field(
            'pexpress_order_confirmed_enable',
            __('Order Confirmed Alert', 'pexpress'),
            array($this, 'render_template_enable_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'order_confirmed')
        );

        add_settings_field(
            'pexpress_order_confirmed_template',
            __('Order Confirmed Template', 'pexpress'),
            array($this, 'render_template_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'order_confirmed')
        );

        // Order Proceeded Template
        add_settings_field(
            'pexpress_order_proceeded_enable',
            __('Order Proceeded Alert', 'pexpress'),
            array($this, 'render_template_enable_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'order_proceeded')
        );

        add_settings_field(
            'pexpress_order_proceeded_template',
            __('Order Proceeded Template', 'pexpress'),
            array($this, 'render_template_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'order_proceeded')
        );

        // Out for Delivery Template
        add_settings_field(
            'pexpress_out_for_delivery_enable',
            __('Out for Delivery Alert', 'pexpress'),
            array($this, 'render_template_enable_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'out_for_delivery')
        );

        add_settings_field(
            'pexpress_out_for_delivery_template',
            __('Out for Delivery Template', 'pexpress'),
            array($this, 'render_template_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'out_for_delivery')
        );

        // Order Completed Template
        add_settings_field(
            'pexpress_order_completed_enable',
            __('Order Completed Alert', 'pexpress'),
            array($this, 'render_template_enable_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'order_completed')
        );

        add_settings_field(
            'pexpress_order_completed_template',
            __('Order Completed Template', 'pexpress'),
            array($this, 'render_template_field'),
            'polar-express-settings',
            'pexpress_sms_templates_section',
            array('template_key' => 'order_completed')
        );
    }

    /**
     * Email templates section callback
     */
    public function email_templates_section_callback()
    {
        echo '<div style="background: linear-gradient(135deg, #f0f6fc 0%, #e8f4fd 100%); padding: 24px; margin: 0 0 32px 0; border-radius: 12px; border: 2px solid #2271b1; box-shadow: 0 2px 8px rgba(34,113,177,0.1);">';
        echo '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; cursor: pointer;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === \'none\' ? \'block\' : \'none\'; this.querySelector(\'.pexpress-toggle-icon\').style.transform = this.nextElementSibling.style.display === \'none\' ? \'rotate(0deg)\' : \'rotate(180deg)\';">';
        echo '<div style="display: flex; align-items: center; gap: 12px;">';
        echo '<span class="dashicons dashicons-info" style="color: #2271b1; font-size: 24px; width: 24px; height: 24px;"></span>';
        echo '<h4 style="margin: 0; font-size: 18px; font-weight: 700; color: #1d2327;">' . esc_html__('Available Placeholders', 'pexpress') . '</h4>';
        echo '</div>';
        echo '<span class="dashicons dashicons-arrow-down-alt2 pexpress-toggle-icon" style="color: #2271b1; font-size: 20px; width: 20px; height: 20px; transition: transform 0.3s ease;"></span>';
        echo '</div>';
        echo '<div id="pexpress-email-placeholders-content" style="display: none;">';
        echo '<p style="margin: 0 0 20px 0; color: #646970; font-size: 14px; line-height: 1.6;">' . esc_html__('Configure HTML email templates for order notifications. You can use HTML tags and placeholders to create rich email templates.', 'pexpress') . '</p>';
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; font-size: 13px;">';
        echo '<div style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #dcdcde;"><strong style="display: block; color: #1d2327; margin-bottom: 10px; font-size: 14px;">' . esc_html__('Order Information', 'pexpress') . '</strong><ul style="margin: 0; padding-left: 20px; color: #646970; line-height: 1.8;"><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_id}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_number}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_date}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_status}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_total}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_subtotal}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_tax}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_shipping}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_discount}}</code></li></ul></div>';
        echo '<div style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #dcdcde;"><strong style="display: block; color: #1d2327; margin-bottom: 10px; font-size: 14px;">' . esc_html__('Customer Information', 'pexpress') . '</strong><ul style="margin: 0; padding-left: 20px; color: #646970; line-height: 1.8;"><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{customer_name}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{customer_email}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{customer_phone}}</code></li></ul></div>';
        echo '<div style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #dcdcde;"><strong style="display: block; color: #1d2327; margin-bottom: 10px; font-size: 14px;">' . esc_html__('Billing Address', 'pexpress') . '</strong><ul style="margin: 0; padding-left: 20px; color: #646970; line-height: 1.8;"><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_address}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_first_name}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_last_name}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_company}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_address_1}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_address_2}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_city}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_state}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_postcode}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{billing_country}}</code></li></ul></div>';
        echo '<div style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #dcdcde;"><strong style="display: block; color: #1d2327; margin-bottom: 10px; font-size: 14px;">' . esc_html__('Shipping Address', 'pexpress') . '</strong><ul style="margin: 0; padding-left: 20px; color: #646970; line-height: 1.8;"><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_address}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_first_name}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_last_name}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_company}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_address_1}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_address_2}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_city}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_state}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_postcode}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{shipping_country}}</code></li></ul></div>';
        echo '<div style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #dcdcde;"><strong style="display: block; color: #1d2327; margin-bottom: 10px; font-size: 14px;">' . esc_html__('Payment & Items', 'pexpress') . '</strong><ul style="margin: 0; padding-left: 20px; color: #646970; line-height: 1.8;"><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{payment_method}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{transaction_id}}</code></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_items}}</code> <span style="color: #8c8f94; font-size: 11px;">(HTML table)</span></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_items_text}}</code> <span style="color: #8c8f94; font-size: 11px;">(plain text)</span></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_notes}}</code> <span style="color: #8c8f94; font-size: 11px;">(HTML)</span></li><li><code style="background: #f6f7f7; padding: 2px 6px; border-radius: 4px; font-size: 12px;">{{order_notes_text}}</code> <span style="color: #8c8f94; font-size: 11px;">(plain text)</span></li></ul></div>';
        echo '</div>';
        echo '<div style="margin-top: 20px; padding: 16px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffb900;">';
        echo '<p style="margin: 0; font-size: 13px; color: #1d2327; line-height: 1.6;"><strong style="color: #856404;">' . esc_html__('Note:', 'pexpress') . '</strong> ' . esc_html__('Templates support full HTML. Use {{order_items}} to display a formatted table of order items. The email will automatically be wrapped in a professional HTML structure.', 'pexpress') . '</p>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * SMS templates section callback
     */
    public function sms_templates_section_callback()
    {
        echo '<div style="background: linear-gradient(135deg, #f0f6fc 0%, #e8f4fd 100%); padding: 24px; margin: 0 0 32px 0; border-radius: 12px; border: 2px solid #00a32a; box-shadow: 0 2px 8px rgba(0,163,42,0.1);">';
        echo '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; cursor: pointer;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === \'none\' ? \'block\' : \'none\'; this.querySelector(\'.pexpress-toggle-icon\').style.transform = this.nextElementSibling.style.display === \'none\' ? \'rotate(0deg)\' : \'rotate(180deg)\';">';
        echo '<div style="display: flex; align-items: center; gap: 12px;">';
        echo '<span class="dashicons dashicons-info" style="color: #00a32a; font-size: 24px; width: 24px; height: 24px;"></span>';
        echo '<h4 style="margin: 0; font-size: 18px; font-weight: 700; color: #1d2327;">' . esc_html__('SMS Template Placeholders', 'pexpress') . '</h4>';
        echo '</div>';
        echo '<span class="dashicons dashicons-arrow-down-alt2 pexpress-toggle-icon" style="color: #00a32a; font-size: 20px; width: 20px; height: 20px; transition: transform 0.3s ease;"></span>';
        echo '</div>';
        echo '<div id="pexpress-sms-placeholders-content" style="display: none;">';
        echo '<p style="margin: 0; color: #646970; font-size: 14px; line-height: 1.6;">' . esc_html__('Configure SMS templates for order notifications. Use placeholders:', 'pexpress') . ' <code style="background: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #2271b1;">{{order_id}}</code>, <code style="background: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #2271b1;">{{customer_name}}</code>, <code style="background: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #2271b1;">{{order_total}}</code>, <code style="background: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #2271b1;">{{order_date}}</code></p>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render email templates section
     */
    public function render_email_templates_section()
    {
        $this->email_templates_section_callback();
        $template_keys = array('order_confirmed', 'order_proceeded', 'out_for_delivery', 'order_completed');

        foreach ($template_keys as $template_key) {
            echo '<div class="polar-task-item" style="margin: 0 0 24px 0; padding: 24px; background: #fff; border: 2px solid #f0f0f1; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: all 0.3s ease;">';
            echo '<div style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1d2327;">' . esc_html(ucwords(str_replace('_', ' ', $template_key))) . '</h3>';
            $this->render_email_template_enable_field(array('template_key' => $template_key));
            echo '</div>';
            echo '<div style="margin-top: 20px;">';
            $this->render_email_template_field(array('template_key' => $template_key));
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Render SMS templates section
     */
    public function render_sms_templates_section()
    {
        $this->sms_templates_section_callback();
        $template_keys = array('order_confirmed', 'order_proceeded', 'out_for_delivery', 'order_completed');

        foreach ($template_keys as $template_key) {
            echo '<div class="polar-task-item" style="margin: 0 0 24px 0; padding: 24px; background: #fff; border: 2px solid #f0f0f1; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: all 0.3s ease;">';
            echo '<div style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f0f0f1;">';
            echo '<h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1d2327;">' . esc_html(ucwords(str_replace('_', ' ', $template_key))) . '</h3>';
            $this->render_template_enable_field(array('template_key' => $template_key));
            echo '</div>';
            echo '<div style="margin-top: 20px;">';
            $this->render_template_field(array('template_key' => $template_key));
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Render email template enable field
     */
    public function render_email_template_enable_field($args)
    {
        $options = get_option('pexpress_options', array());
        $template_key = $args['template_key'];
        $enabled = isset($options['email_templates'][$template_key]['enabled']) ? $options['email_templates'][$template_key]['enabled'] : '';

        $checked = !empty($enabled) ? 'checked' : '';
        echo '<div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #f6f7f7; border-radius: 8px; border: 2px solid ' . ($checked ? '#ffb900' : '#dcdcde') . '; transition: all 0.2s ease;">';
        echo '<input type="checkbox" id="pexpress_email_' . esc_attr($template_key) . '_enable" name="pexpress_options[email_templates][' . esc_attr($template_key) . '][enabled]" value="1" ' . $checked . ' style="width: 18px; height: 18px; cursor: pointer; flex-shrink: 0;" />';
        echo '<label for="pexpress_email_' . esc_attr($template_key) . '_enable" style="font-weight: 600; color: #1d2327; font-size: 14px; cursor: pointer; margin: 0;">' . esc_html__('Enable this notification', 'pexpress') . '</label>';
        echo '</div>';
    }

    /**
     * Render email template field
     */
    public function render_email_template_field($args)
    {
        $options = get_option('pexpress_options', array());
        $template_key = $args['template_key'];

        // Enhanced default templates with full order details
        $default_templates = array(
            'order_confirmed' => '<h2 style="color: #2271b1; margin-top: 0;">' . __('Order Confirmed', 'pexpress') . '</h2>
<p>Dear {{customer_name}},</p>
<p>Your order #{{order_number}} has been confirmed. Thank you for your order!</p>

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Order Details', 'pexpress') . '</h3>
<p><strong>' . __('Order Number:', 'pexpress') . '</strong> #{{order_number}}<br>
<strong>' . __('Order Date:', 'pexpress') . '</strong> {{order_date}}<br>
<strong>' . __('Order Status:', 'pexpress') . '</strong> {{order_status}}</p>

{{order_items}}

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Order Summary', 'pexpress') . '</h3>
<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . __('Subtotal:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">{{order_subtotal}}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . __('Shipping:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">{{order_shipping}}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . __('Tax:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">{{order_tax}}</td>
    </tr>
    <tr>
        <td style="padding: 8px;"><strong>' . __('Total:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right;"><strong>{{order_total}}</strong></td>
    </tr>
</table>

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Billing Address', 'pexpress') . '</h3>
<p>{{billing_address}}</p>

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Shipping Address', 'pexpress') . '</h3>
<p>{{shipping_address}}</p>

<p style="margin-top: 30px;">' . __('We will keep you updated on your order status.', 'pexpress') . '</p>
<p>' . __('Thank you for choosing us!', 'pexpress') . '</p>',
            'order_proceeded' => '<h2 style="color: #2271b1; margin-top: 0;">' . __('Order Being Processed', 'pexpress') . '</h2>
<p>Dear {{customer_name}},</p>
<p>Your order #{{order_number}} is now being processed. We will update you soon.</p>

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Order Details', 'pexpress') . '</h3>
<p><strong>' . __('Order Number:', 'pexpress') . '</strong> #{{order_number}}<br>
<strong>' . __('Order Date:', 'pexpress') . '</strong> {{order_date}}<br>
<strong>' . __('Order Status:', 'pexpress') . '</strong> {{order_status}}</p>

{{order_items}}

<p style="margin-top: 30px;">' . __('We are working on your order and will notify you once it\'s ready for delivery.', 'pexpress') . '</p>',
            'out_for_delivery' => '<h2 style="color: #2271b1; margin-top: 0;">' . __('Out for Delivery', 'pexpress') . '</h2>
<p>Dear {{customer_name}},</p>
<p>Great news! Your order #{{order_number}} is out for delivery. You will receive it shortly.</p>

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Order Details', 'pexpress') . '</h3>
<p><strong>' . __('Order Number:', 'pexpress') . '</strong> #{{order_number}}<br>
<strong>' . __('Order Date:', 'pexpress') . '</strong> {{order_date}}<br>
<strong>' . __('Order Status:', 'pexpress') . '</strong> {{order_status}}</p>

{{order_items}}

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Delivery Address', 'pexpress') . '</h3>
<p>{{shipping_address}}</p>

<p style="margin-top: 30px;">' . __('Please ensure someone is available to receive the delivery.', 'pexpress') . '</p>',
            'order_completed' => '<h2 style="color: #2271b1; margin-top: 0;">' . __('Order Completed', 'pexpress') . '</h2>
<p>Dear {{customer_name}},</p>
<p>Your order #{{order_number}} has been completed. Thank you for choosing us!</p>

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Order Details', 'pexpress') . '</h3>
<p><strong>' . __('Order Number:', 'pexpress') . '</strong> #{{order_number}}<br>
<strong>' . __('Order Date:', 'pexpress') . '</strong> {{order_date}}<br>
<strong>' . __('Order Status:', 'pexpress') . '</strong> {{order_status}}</p>

{{order_items}}

<h3 style="color: #1d2327; margin-top: 25px;">' . __('Order Summary', 'pexpress') . '</h3>
<table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . __('Subtotal:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">{{order_subtotal}}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . __('Shipping:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">{{order_shipping}}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . __('Tax:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">{{order_tax}}</td>
    </tr>
    <tr>
        <td style="padding: 8px;"><strong>' . __('Total:', 'pexpress') . '</strong></td>
        <td style="padding: 8px; text-align: right;"><strong>{{order_total}}</strong></td>
    </tr>
</table>

<p style="margin-top: 30px;">' . __('We hope you enjoy your purchase!', 'pexpress') . '</p>
<p>' . __('Thank you for your business!', 'pexpress') . '</p>',
        );

        $template = isset($options['email_templates'][$template_key]['template']) ? $options['email_templates'][$template_key]['template'] : (isset($default_templates[$template_key]) ? $default_templates[$template_key] : '');

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<label for="pexpress_email_' . esc_attr($template_key) . '_template" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html__('Email Template', 'pexpress') . '</label>';
        // Output raw HTML for email templates (already sanitized with wp_kses_post on save)
        echo '<textarea id="pexpress_email_' . esc_attr($template_key) . '_template" name="pexpress_options[email_templates][' . esc_attr($template_key) . '][template]" rows="15" style="width: 100%; padding: 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 13px; font-family: \'Monaco\', \'Menlo\', \'Ubuntu Mono\', \'Consolas\', monospace; line-height: 1.6; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box; resize: vertical;">' . esc_textarea($template) . '</textarea>';
        echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('HTML templates are supported. Use placeholders like {{order_id}}, {{customer_name}}, {{order_items}}, etc. See the section above for all available placeholders.', 'pexpress') . '</p>';
        echo '</div>';
    }

    /**
     * Render template enable field
     */
    public function render_template_enable_field($args)
    {
        $options = get_option('pexpress_options', array());
        $template_key = $args['template_key'];
        $enabled = isset($options['sms_templates'][$template_key]['enabled']) ? $options['sms_templates'][$template_key]['enabled'] : '';

        $checked = !empty($enabled) ? 'checked' : '';
        echo '<div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #f6f7f7; border-radius: 8px; border: 2px solid ' . ($checked ? '#ffb900' : '#dcdcde') . '; transition: all 0.2s ease;">';
        echo '<input type="checkbox" id="pexpress_' . esc_attr($template_key) . '_enable" name="pexpress_options[sms_templates][' . esc_attr($template_key) . '][enabled]" value="1" ' . $checked . ' style="width: 18px; height: 18px; cursor: pointer; flex-shrink: 0;" />';
        echo '<label for="pexpress_' . esc_attr($template_key) . '_enable" style="font-weight: 600; color: #1d2327; font-size: 14px; cursor: pointer; margin: 0;">' . esc_html__('Enable this notification', 'pexpress') . '</label>';
        echo '</div>';
    }

    /**
     * Render template field
     */
    public function render_template_field($args)
    {
        $options = get_option('pexpress_options', array());
        $template_key = $args['template_key'];

        $default_templates = array(
            'order_confirmed' => __('Your order #{{order_id}} has been confirmed. Thank you for your order!', 'pexpress'),
            'order_proceeded' => __('Your order #{{order_id}} is now being processed. We will update you soon.', 'pexpress'),
            'out_for_delivery' => __('Your order #{{order_id}} is out for delivery. You will receive it shortly.', 'pexpress'),
            'order_completed' => __('Your order #{{order_id}} has been completed. Thank you for choosing us!', 'pexpress'),
        );

        $template = isset($options['sms_templates'][$template_key]['template']) ? $options['sms_templates'][$template_key]['template'] : (isset($default_templates[$template_key]) ? $default_templates[$template_key] : '');

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<label for="pexpress_' . esc_attr($template_key) . '_template" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html__('SMS Template', 'pexpress') . '</label>';
        echo '<textarea id="pexpress_' . esc_attr($template_key) . '_template" name="pexpress_options[sms_templates][' . esc_attr($template_key) . '][template]" rows="4" style="width: 100%; padding: 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 14px; line-height: 1.6; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box; resize: vertical;">' . esc_textarea($template) . '</textarea>';
        echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('Available placeholders: {{order_id}}, {{customer_name}}, {{order_total}}, {{order_date}}', 'pexpress') . '</p>';
        echo '</div>';
    }
}
