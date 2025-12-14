<?php

/**
 * SMS Settings Module
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SMS Settings handler
 */
class PExpress_Admin_Settings_SMS
{
    /**
     * Register SMS settings
     */
    public function register_settings()
    {
        add_settings_section(
            'pexpress_sms_section',
            __('SMS Configuration', 'pexpress'),
            array($this, 'sms_section_callback'),
            'polar-express-settings'
        );

        add_settings_field(
            'pexpress_enable_plugin',
            __('Enable Plugin', 'pexpress'),
            array($this, 'render_enable_plugin_field'),
            'polar-express-settings',
            'pexpress_sms_section'
        );

        add_settings_field(
            'pexpress_api_hash_token',
            __('API Hash Token', 'pexpress'),
            array($this, 'render_api_hash_token_field'),
            'polar-express-settings',
            'pexpress_sms_section'
        );

        add_settings_field(
            'pexpress_api_url',
            __('API URL', 'pexpress'),
            array($this, 'render_api_url_field'),
            'polar-express-settings',
            'pexpress_sms_section'
        );

        add_settings_field(
            'pexpress_api_sid',
            __('SID/Stakeholder', 'pexpress'),
            array($this, 'render_api_sid_field'),
            'polar-express-settings',
            'pexpress_sms_section'
        );

        add_settings_field(
            'pexpress_enable_unicode',
            __('Unicode/Bangla SMS', 'pexpress'),
            array($this, 'render_enable_unicode_field'),
            'polar-express-settings',
            'pexpress_sms_section'
        );
    }

    /**
     * SMS section callback
     */
    public function sms_section_callback()
    {
        echo '<hr>';
    }

    /**
     * Render SMS configuration section
     */
    public function render_section()
    {
        echo '<div class="pexpress-form-fields-container">';
        echo '<div style="margin-bottom: 24px;">';
        $this->render_enable_plugin_field();
        echo '</div>';
        echo '<div class="pexpress-settings-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 24px;">';
        echo '<div>';
        $this->render_api_hash_token_field();
        echo '</div>';
        echo '<div>';
        $this->render_api_url_field();
        echo '</div>';
        echo '</div>';
        echo '<div class="pexpress-settings-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 24px;">';
        echo '<div>';
        $this->render_api_sid_field();
        echo '</div>';
        echo '<div>';
        $this->render_enable_unicode_field();
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render enable plugin field
     */
    public function render_enable_plugin_field()
    {
        $options = get_option('pexpress_options', array());
        $enable_plugin = isset($options['sms_config']['enable_plugin']) ? $options['sms_config']['enable_plugin'] : '';
        $checked = !empty($enable_plugin) ? 'checked' : '';

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<div style="display: flex; align-items: flex-start; gap: 12px; padding: 16px; background: #f6f7f7; border-radius: 8px; border: 2px solid ' . ($checked ? '#00a32a' : '#dcdcde') . '; transition: all 0.2s ease;">';
        echo '<input type="checkbox" id="pexpress_enable_plugin" name="pexpress_options[sms_config][enable_plugin]" value="1" ' . $checked . ' style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer; flex-shrink: 0;" />';
        echo '<div style="flex: 1;">';
        echo '<label for="pexpress_enable_plugin" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 4px; font-size: 14px; cursor: pointer;">' . esc_html__('Enable Plugin', 'pexpress') . '</label>';
        echo '<p class="description" style="margin: 0; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('Enable the SMS plugin functionality.', 'pexpress') . '</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render API hash token field
     */
    public function render_api_hash_token_field()
    {
        $options = get_option('pexpress_options', array());
        $api_hash_token = isset($options['sms_config']['api_hash_token']) ? esc_attr($options['sms_config']['api_hash_token']) : '';

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<label for="pexpress_api_hash_token" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html__('API Hash Token', 'pexpress') . '</label>';
        echo '<input type="text" id="pexpress_api_hash_token" name="pexpress_options[sms_config][api_hash_token]" value="' . $api_hash_token . '" placeholder="' . esc_attr__('Get it from Panel Profile', 'pexpress') . '" style="width: 100%; padding: 12px 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 14px; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box;" />';
        echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('Get it from Panel Profile.', 'pexpress') . '</p>';
        echo '</div>';
    }

    /**
     * Render API URL field
     */
    public function render_api_url_field()
    {
        $options = get_option('pexpress_options', array());
        $api_url = isset($options['sms_config']['api_url']) ? esc_url($options['sms_config']['api_url']) : '';

        if (empty($api_url)) {
            $api_url = 'https://smsplus.sslwireless.com/api/v3/send-sms';
        }

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<label for="pexpress_api_url" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html__('API URL', 'pexpress') . '</label>';
        echo '<input type="text" id="pexpress_api_url" name="pexpress_options[sms_config][api_url]" value="' . esc_attr($api_url) . '" style="width: 100%; padding: 12px 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 14px; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box; font-family: monospace;" />';
        echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('Must input this field.', 'pexpress') . '</p>';
        echo '</div>';
    }

    /**
     * Render API SID field
     */
    public function render_api_sid_field()
    {
        $options = get_option('pexpress_options', array());
        $api_sid = isset($options['sms_config']['api_sid']) ? esc_attr($options['sms_config']['api_sid']) : 'POLAROTP';

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<label for="pexpress_api_sid" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html__('SID/Stakeholder', 'pexpress') . '</label>';
        echo '<input type="text" id="pexpress_api_sid" name="pexpress_options[sms_config][api_sid]" value="' . $api_sid . '" placeholder="' . esc_attr__('Provided from Ethertech WOOTP', 'pexpress') . '" style="width: 100%; padding: 12px 16px; border: 2px solid #dcdcde; border-radius: 8px; font-size: 14px; transition: all 0.2s ease; background: #fff; color: #1d2327; box-sizing: border-box;" />';
        echo '<p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('SID/Stakeholder (Provided from Ethertech WOOTP).', 'pexpress') . '</p>';
        echo '</div>';
    }

    /**
     * Render enable unicode field
     */
    public function render_enable_unicode_field()
    {
        $options = get_option('pexpress_options', array());
        $enable_unicode = isset($options['sms_config']['enable_unicode']) ? $options['sms_config']['enable_unicode'] : '';
        $checked = !empty($enable_unicode) ? 'checked' : '';

        echo '<div class="pexpress-form-field-wrapper">';
        echo '<label for="pexpress_enable_unicode" style="display: block; font-weight: 600; color: #1d2327; margin-bottom: 8px; font-size: 14px;">' . esc_html__('Unicode/Bangla SMS', 'pexpress') . '</label>';
        echo '<div style="display: flex; align-items: flex-start; gap: 12px; padding: 16px; background: #f6f7f7; border-radius: 8px; border: 2px solid ' . ($checked ? '#00a32a' : '#dcdcde') . '; transition: all 0.2s ease;">';
        echo '<input type="checkbox" id="pexpress_enable_unicode" name="pexpress_options[sms_config][enable_unicode]" value="1" ' . $checked . ' style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer; flex-shrink: 0;" />';
        echo '<div style="flex: 1;">';
        echo '<p class="description" style="margin: 0; color: #646970; font-size: 13px; line-height: 1.5;">' . esc_html__('Enable Unicode/Bangla SMS support.', 'pexpress') . '</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
