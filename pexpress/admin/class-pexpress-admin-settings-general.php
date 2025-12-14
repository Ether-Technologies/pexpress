<?php

/**
 * General Settings Module
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * General Settings handler
 */
class PExpress_Admin_Settings_General
{
    /**
     * Register general settings
     */
    public function register_settings()
    {
        add_settings_section(
            'pexpress_general_section',
            __('General Settings', 'pexpress'),
            array($this, 'general_section_callback'),
            'polar-express-settings'
        );

        add_settings_field(
            'pexpress_heartbeat_interval',
            __('Heartbeat Interval (seconds)', 'pexpress'),
            array($this, 'render_number_field'),
            'polar-express-settings',
            'pexpress_general_section',
            array(
                'label_for' => 'pexpress_heartbeat_interval',
                'option_key' => 'heartbeat_interval',
                'description' => __('How often to check for updates (default: 15 seconds)', 'pexpress'),
                'default' => 15,
                'min' => 5,
                'max' => 60
            )
        );
    }

    /**
     * General section callback
     */
    public function general_section_callback()
    {
        echo '<p>' . esc_html__('General plugin settings and configuration.', 'pexpress') . '</p>';
    }

    /**
     * Render general section
     */
    public function render_section()
    {
        $this->general_section_callback();
        echo '<table class="form-table">';
        $this->render_number_field(array(
            'label_for' => 'pexpress_heartbeat_interval',
            'option_key' => 'heartbeat_interval',
            'description' => __('How often to check for updates (default: 15 seconds)', 'pexpress'),
            'default' => 15,
            'min' => 5,
            'max' => 60
        ));
        echo '</table>';
    }

    /**
     * Render number field
     */
    public function render_number_field($args)
    {
        $options = get_option('pexpress_options', array());
        $value = isset($options[$args['option_key']]) ? intval($options[$args['option_key']]) : ($args['default'] ?? 15);
        $min = isset($args['min']) ? $args['min'] : 1;
        $max = isset($args['max']) ? $args['max'] : 100;
        echo '<input type="number" id="' . esc_attr($args['label_for']) . '" name="pexpress_options[' . esc_attr($args['option_key']) . ']" value="' . $value . '" min="' . $min . '" max="' . $max . '" class="small-text" />';
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
}

