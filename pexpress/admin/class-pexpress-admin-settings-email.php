<?php

/**
 * Email Settings Module
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Settings handler
 */
class PExpress_Admin_Settings_Email
{
    /**
     * Register email settings
     */
    public function register_settings()
    {
        add_settings_section(
            'pexpress_email_section',
            __('Email Configuration', 'pexpress'),
            array($this, 'email_section_callback'),
            'polar-express-settings'
        );

        add_settings_field(
            'pexpress_enable_email',
            __('Enable Email Notifications', 'pexpress'),
            array($this, 'render_checkbox_field'),
            'polar-express-settings',
            'pexpress_email_section',
            array(
                'label_for' => 'pexpress_enable_email',
                'option_key' => 'email_config.enable_email',
                'description' => __('Enable email notifications for order status updates', 'pexpress')
            )
        );

        add_settings_field(
            'pexpress_email_from_name',
            __('From Name', 'pexpress'),
            array($this, 'render_text_field'),
            'polar-express-settings',
            'pexpress_email_section',
            array(
                'label_for' => 'pexpress_email_from_name',
                'option_key' => 'email_config.from_name',
                'description' => __('Name to use as sender', 'pexpress')
            )
        );

        add_settings_field(
            'pexpress_email_from_email',
            __('From Email', 'pexpress'),
            array($this, 'render_text_field'),
            'polar-express-settings',
            'pexpress_email_section',
            array(
                'label_for' => 'pexpress_email_from_email',
                'option_key' => 'email_config.from_email',
                'description' => __('Email address to use as sender', 'pexpress')
            )
        );

        add_settings_field(
            'pexpress_mailgun_enable',
            __('Enable Mailgun', 'pexpress'),
            array($this, 'render_checkbox_field'),
            'polar-express-settings',
            'pexpress_email_section',
            array(
                'label_for' => 'pexpress_mailgun_enable',
                'option_key' => 'mailgun_config.enable_mailgun',
                'description' => __('Enable Mailgun API for sending emails (recommended for better deliverability)', 'pexpress')
            )
        );

        add_settings_field(
            'pexpress_mailgun_api_key',
            __('Mailgun API Key', 'pexpress'),
            array($this, 'render_password_field'),
            'polar-express-settings',
            'pexpress_email_section',
            array(
                'label_for' => 'pexpress_mailgun_api_key',
                'option_key' => 'mailgun_config.api_key',
                'description' => __('Your Mailgun Private API key', 'pexpress')
            )
        );

        add_settings_field(
            'pexpress_mailgun_domain',
            __('Mailgun Domain', 'pexpress'),
            array($this, 'render_text_field'),
            'polar-express-settings',
            'pexpress_email_section',
            array(
                'label_for' => 'pexpress_mailgun_domain',
                'option_key' => 'mailgun_config.domain',
                'description' => __('Your Mailgun sending domain (e.g., mail.ethertech.ltd)', 'pexpress')
            )
        );

        add_settings_field(
            'pexpress_mailgun_region',
            __('Mailgun Region', 'pexpress'),
            array($this, 'render_mailgun_region_field'),
            'polar-express-settings',
            'pexpress_email_section'
        );
    }

    /**
     * Email section callback
     */
    public function email_section_callback()
    {
        echo '<hr>';
        echo '<p>' . esc_html__('Configure email notification settings.', 'pexpress') . '</p>';
    }

    /**
     * Render email configuration section
     */
    public function render_section()
    {
        echo '<div class="pexpress-form-fields-container">';
        echo '<div style="margin-bottom: 24px;">';
        $this->render_checkbox_field(array(
            'label_for' => 'pexpress_enable_email',
            'option_key' => 'email_config.enable_email',
            'label' => __('Enable Email Notifications', 'pexpress'),
            'description' => __('Enable email notifications for order status updates', 'pexpress')
        ));
        echo '</div>';
        echo '<div class="pexpress-settings-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 24px;">';
        echo '<div>';
        $this->render_text_field(array(
            'label_for' => 'pexpress_email_from_name',
            'option_key' => 'email_config.from_name',
            'label' => __('From Name', 'pexpress'),
            'description' => __('Name to use as sender', 'pexpress')
        ));
        echo '</div>';
        echo '<div>';
        $this->render_text_field(array(
            'label_for' => 'pexpress_email_from_email',
            'option_key' => 'email_config.from_email',
            'label' => __('From Email', 'pexpress'),
            'description' => __('Email address to use as sender', 'pexpress')
        ));
        echo '</div>';
        echo '</div>';
        echo '<div style="margin-bottom: 24px;">';
        $this->render_checkbox_field(array(
            'label_for' => 'pexpress_mailgun_enable',
            'option_key' => 'mailgun_config.enable_mailgun',
            'label' => __('Enable Mailgun', 'pexpress'),
            'description' => __('Enable Mailgun API for sending emails (recommended for better deliverability)', 'pexpress')
        ));
        echo '</div>';
        echo '<div class="pexpress-settings-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 24px;">';
        echo '<div>';
        $this->render_password_field(array(
            'label_for' => 'pexpress_mailgun_api_key',
            'option_key' => 'mailgun_config.api_key',
            'label' => __('Mailgun API Key', 'pexpress'),
            'description' => __('Your Mailgun Private API key', 'pexpress')
        ));
        echo '</div>';
        echo '<div>';
        $this->render_text_field(array(
            'label_for' => 'pexpress_mailgun_domain',
            'option_key' => 'mailgun_config.domain',
            'label' => __('Mailgun Domain', 'pexpress'),
            'description' => __('Your Mailgun sending domain (e.g., mail.ethertech.ltd)', 'pexpress')
        ));
        echo '</div>';
        echo '</div>';
        echo '<div style="margin-bottom: 24px;">';
        $this->render_mailgun_region_field();
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render text field
     */
    private function render_text_field($args)
    {
        $options = get_option('pexpress_options', array());

        if (strpos($args['option_key'], '.') !== false) {
            $keys = explode('.', $args['option_key']);
            $value = $options;
            foreach ($keys as $key) {
                $value = isset($value[$key]) ? $value[$key] : '';
            }
            $value = esc_attr($value);
            $name = 'pexpress_options[' . implode('][', $keys) . ']';
        } else {
            $value = isset($options[$args['option_key']]) ? esc_attr($options[$args['option_key']]) : '';
            $name = 'pexpress_options[' . esc_attr($args['option_key']) . ']';
        }

        $label = isset($args['label']) ? $args['label'] : '';
        if (empty($label) && isset($args['label_for'])) {
            $label = ucwords(str_replace(array('pexpress_', '_'), array('', ' '), $args['label_for']));
        }

        echo '<div class="pexpress-form-field-wrapper">';
        if (!empty($label)) {
            echo '<label for="' . esc_attr($args['label_for']) . '" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html($label) . '</label>';
        }
        echo '<div style="position: relative;">';
        echo '<input type="text" id="' . esc_attr($args['label_for']) . '" name="' . $name . '" value="' . $value . '" style="width: 100%; padding: 12px 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 14px; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box;" />';
        echo '</div>';
        if (!empty($args['description'])) {
            echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
    }

    /**
     * Render password field
     */
    private function render_password_field($args)
    {
        $options = get_option('pexpress_options', array());

        if (strpos($args['option_key'], '.') !== false) {
            $keys = explode('.', $args['option_key']);
            $value = $options;
            foreach ($keys as $key) {
                $value = isset($value[$key]) ? $value[$key] : '';
            }
            $value = esc_attr($value);
            $name = 'pexpress_options[' . implode('][', $keys) . ']';
        } else {
            $value = isset($options[$args['option_key']]) ? esc_attr($options[$args['option_key']]) : '';
            $name = 'pexpress_options[' . esc_attr($args['option_key']) . ']';
        }

        $label = isset($args['label']) ? $args['label'] : '';
        if (empty($label) && isset($args['label_for'])) {
            $label = ucwords(str_replace(array('pexpress_', '_'), array('', ' '), $args['label_for']));
        }

        echo '<div class="pexpress-form-field-wrapper">';
        if (!empty($label)) {
            echo '<label for="' . esc_attr($args['label_for']) . '" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html($label) . '</label>';
        }
        echo '<div style="position: relative;">';
        echo '<input type="password" id="' . esc_attr($args['label_for']) . '" name="' . $name . '" value="' . $value . '" style="width: 100%; padding: 12px 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 14px; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box; font-family: monospace;" />';
        echo '</div>';
        if (!empty($args['description'])) {
            echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
    }

    /**
     * Render checkbox field
     */
    private function render_checkbox_field($args)
    {
        $options = get_option('pexpress_options', array());

        if (strpos($args['option_key'], '.') !== false) {
            $keys = explode('.', $args['option_key']);
            $value = $options;
            foreach ($keys as $key) {
                $value = isset($value[$key]) ? $value[$key] : '';
            }
            $checked = !empty($value) ? 'checked' : '';
            $name = 'pexpress_options[' . implode('][', $keys) . ']';
        } else {
            $checked = !empty($options[$args['option_key']]) ? 'checked' : '';
            $name = 'pexpress_options[' . esc_attr($args['option_key']) . ']';
        }

        $label = isset($args['label']) ? $args['label'] : '';
        if (empty($label) && isset($args['label_for'])) {
            $label = ucwords(str_replace(array('pexpress_', '_'), array('', ' '), $args['label_for']));
        }

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<div style="display: flex; align-items: flex-start; gap: 12px; padding: 16px; background: #f6f7f7; border-radius: 8px; border: 2px solid ' . ($checked ? '#00a32a' : '#dcdcde') . '; transition: all 0.2s ease;">';
        echo '<input type="checkbox" id="' . esc_attr($args['label_for']) . '" name="' . $name . '" value="1" ' . $checked . ' style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer; flex-shrink: 0;" />';
        echo '<div style="flex: 1;">';
        if (!empty($label)) {
            echo '<label for="' . esc_attr($args['label_for']) . '" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 4px; font-size: 14px; cursor: pointer;">' . esc_html($label) . '</label>';
        }
        if (!empty($args['description'])) {
            echo '<p class="description" style="margin: 0; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render Mailgun region field
     */
    public function render_mailgun_region_field()
    {
        $options = get_option('pexpress_options', array());
        $mailgun_config = isset($options['mailgun_config']) ? $options['mailgun_config'] : array();
        $region = isset($mailgun_config['region']) ? $mailgun_config['region'] : 'us';

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<label for="pexpress_mailgun_region" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html__('Mailgun Region', 'pexpress') . '</label>';
        echo '<select id="pexpress_mailgun_region" name="pexpress_options[mailgun_config][region]" style="width: 100%; padding: 12px 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 14px; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box; cursor: pointer;">';
        echo '<option value="us"' . selected('us', $region, false) . '>' . esc_html__('U.S./North America', 'pexpress') . '</option>';
        echo '<option value="eu"' . selected('eu', $region, false) . '>' . esc_html__('Europe', 'pexpress') . '</option>';
        echo '</select>';
        echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('Choose the Mailgun region for your sending domain', 'pexpress') . '</p>';
        echo '</div>';
    }
}
