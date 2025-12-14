<?php

/**
 * Admin pages (Setup Guideline and Changelog)
 *
 * @package PExpress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin pages handler
 */
class PExpress_Admin_Pages
{

    /**
     * Render Setup Guideline page
     */
    public function render_setup_guideline_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $shortcodes = array(
            'polar_agency' => array(
                'name' => __('Agency Dashboard', 'pexpress'),
                'shortcode' => '[polar_agency]',
                'alias' => '[polar_hr]',
                'description' => __('Full access to assign orders and manage operations', 'pexpress'),
                'page_suggestion' => '/agency-dashboard',
                'role' => 'polar_hr',
            ),
            'polar_sr' => array(
                'name' => __('SR Dashboard', 'pexpress'),
                'shortcode' => '[polar_sr]',
                'alias' => '[polar_delivery]',
                'description' => __('Can view and update delivery status for assigned orders', 'pexpress'),
                'page_suggestion' => '/sr-dashboard',
                'role' => 'polar_delivery',
            ),
            'polar_fridge' => array(
                'name' => __('Fridge Provider Dashboard', 'pexpress'),
                'shortcode' => '[polar_fridge]',
                'description' => __('Can view and mark fridge collection for assigned orders', 'pexpress'),
                'page_suggestion' => '/fridge-tasks',
                'role' => 'polar_fridge',
            ),
            'polar_product_provider' => array(
                'name' => __('Product Provider Dashboard', 'pexpress'),
                'shortcode' => '[polar_product_provider]',
                'alias' => '[polar_distributor]',
                'description' => __('Can view and mark fulfillment for assigned orders', 'pexpress'),
                'page_suggestion' => '/product-provider-dashboard',
                'role' => 'polar_distributor',
            ),
            'polar_support' => array(
                'name' => __('Support Dashboard', 'pexpress'),
                'shortcode' => '[polar_support]',
                'description' => __('Can view all orders and provide customer support', 'pexpress'),
                'page_suggestion' => '/support-dashboard',
                'role' => 'polar_support',
            ),
        );

        $customer_shortcodes = array(
            'polar_order_tracking' => array(
                'name' => __('Order Tracking', 'pexpress'),
                'shortcode' => '[polar_order_tracking]',
                'description' => __('Display real-time order tracking status for customers. Shows progress of Agency, SR, Fridge Provider, and Product Provider.', 'pexpress'),
                'page_suggestion' => '/order-tracking',
                'attributes' => __('Optional: order_id="123" or use ?order_id=123 in URL', 'pexpress'),
            ),
            'polar_order_information' => array(
                'name' => __('Order Information', 'pexpress'),
                'shortcode' => '[polar_order_information]',
                'description' => __('Display complete order details including customer info, addresses, meeting information, and order items.', 'pexpress'),
                'page_suggestion' => '/order-information',
                'attributes' => __('Optional: order_id="123" or use ?order_id=123 in URL', 'pexpress'),
            ),
        );

        $this->render_setup_guideline_html($shortcodes, $customer_shortcodes);
    }

    /**
     * Render Setup Guideline HTML
     */
    private function render_setup_guideline_html($shortcodes, $customer_shortcodes = array())
    {
?>
        <div class="wrap polar-setup-guideline">

            <div class="polar-setup-header">
                <h1><?php esc_html_e('Polar Express Setup Guideline', 'pexpress'); ?></h1>
                <p><?php esc_html_e('Follow these steps to set up and configure Polar Express for your team.', 'pexpress'); ?></p>
            </div>

            <div class="polar-setup-section">
                <h2><?php esc_html_e('📋 Quick Setup Checklist', 'pexpress'); ?></h2>
                <ul class="polar-checklist">
                    <li><?php esc_html_e('Assign users to Polar Express roles', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Create dashboard pages with shortcodes', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Configure SMS settings (optional)', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Test order assignment workflow', 'pexpress'); ?></li>
                </ul>
            </div>

            <div class="polar-setup-section">
                <h2><?php esc_html_e('👥 Step 1: Assign Users to Roles', 'pexpress'); ?></h2>
                <div class="polar-setup-step">
                    <h3><?php esc_html_e('Role Management', 'pexpress'); ?></h3>
                    <ol>
                        <li><?php esc_html_e('Go to Polar Express → Settings', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Click "Add User to Role" button', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Select a role from the dropdown', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Select a user to assign to that role', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Click "Add User" to complete the assignment', 'pexpress'); ?></li>
                    </ol>
                    <div class="polar-info-box">
                        <strong><?php esc_html_e('Available Roles:', 'pexpress'); ?></strong>
                        <ul style="margin-top: 10px;">
                            <li><strong><?php esc_html_e('Polar Agency (HR):', 'pexpress'); ?></strong> <?php esc_html_e('Full access to assign orders and manage operations', 'pexpress'); ?></li>
                            <li><strong><?php esc_html_e('Polar SR (Delivery):', 'pexpress'); ?></strong> <?php esc_html_e('Can view and update delivery status for assigned orders', 'pexpress'); ?></li>
                            <li><strong><?php esc_html_e('Polar Fridge Provider:', 'pexpress'); ?></strong> <?php esc_html_e('Can view and mark fridge collection for assigned orders', 'pexpress'); ?></li>
                            <li><strong><?php esc_html_e('Polar Product Provider (Distributor):', 'pexpress'); ?></strong> <?php esc_html_e('Can view and mark fulfillment for assigned orders', 'pexpress'); ?></li>
                            <li><strong><?php esc_html_e('Polar Support:', 'pexpress'); ?></strong> <?php esc_html_e('Can view all orders and provide customer support', 'pexpress'); ?></li>
                        </ul>
                    </div>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-settings')); ?>" class="button button-primary">
                            <?php esc_html_e('Go to Settings →', 'pexpress'); ?>
                        </a>
                    </p>
                </div>
            </div>

            <div class="polar-setup-section">
                <h2><?php esc_html_e('📄 Step 2: Create Dashboard Pages', 'pexpress'); ?></h2>
                <div class="polar-setup-step">
                    <h3><?php esc_html_e('Create Pages for Each Dashboard', 'pexpress'); ?></h3>
                    <ol>
                        <li><?php esc_html_e('Go to Pages → Add New', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Create a new page for each dashboard type', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Paste the corresponding shortcode in the page content', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Publish the page', 'pexpress'); ?></li>
                    </ol>
                    <div class="polar-warning-box">
                        <strong><?php esc_html_e('Important:', 'pexpress'); ?></strong> <?php esc_html_e('Each user will only see their own dashboard based on their assigned role. Make sure users are logged in when accessing these pages.', 'pexpress'); ?>
                    </div>
                </div>

                <h3 style="margin-top: 30px;"><?php esc_html_e('Role Dashboard Shortcodes', 'pexpress'); ?></h3>
                <p style="color: #646970; margin-bottom: 20px;">
                    <?php esc_html_e('These shortcodes are for role-based dashboards. Users must be logged in and have the appropriate role to access their dashboard.', 'pexpress'); ?>
                </p>
                <?php foreach ($shortcodes as $key => $shortcode) : ?>
                    <div class="polar-shortcode-card">
                        <h4><?php echo esc_html($shortcode['name']); ?></h4>
                        <p><?php echo esc_html($shortcode['description']); ?></p>
                        <div class="polar-shortcode-code">
                            <?php echo esc_html($shortcode['shortcode']); ?>
                            <?php if (!empty($shortcode['alias'])) : ?>
                                <span style="color: #646970; font-size: 12px; margin-left: 10px;">
                                    <?php esc_html_e('(also:', 'pexpress'); ?> <?php echo esc_html($shortcode['alias']); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                        <button class="polar-copy-btn" data-shortcode="<?php echo esc_attr($shortcode['shortcode']); ?>">
                            <?php esc_html_e('Copy Shortcode', 'pexpress'); ?>
                        </button>
                        <p style="margin-top: 10px; color: #646970; font-size: 13px;">
                            <strong><?php esc_html_e('Suggested Page Slug:', 'pexpress'); ?></strong>
                            <code><?php echo esc_html($shortcode['page_suggestion']); ?></code>
                            <?php if (!empty($shortcode['role'])) : ?>
                                <br><strong><?php esc_html_e('Required Role:', 'pexpress'); ?></strong>
                                <code><?php echo esc_html($shortcode['role']); ?></code>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($customer_shortcodes)) : ?>
                    <h3 style="margin-top: 40px;"><?php esc_html_e('Customer-Facing Shortcodes', 'pexpress'); ?></h3>
                    <p style="color: #646970; margin-bottom: 20px;">
                        <?php esc_html_e('These shortcodes are for customer pages. Users must be logged in to view their own orders. Order ID can be provided via shortcode attribute or URL parameter.', 'pexpress'); ?>
                    </p>
                    <?php foreach ($customer_shortcodes as $key => $shortcode) : ?>
                        <div class="polar-shortcode-card" style="border-left-color: #2271b1;">
                            <h4><?php echo esc_html($shortcode['name']); ?></h4>
                            <p><?php echo esc_html($shortcode['description']); ?></p>
                            <div class="polar-shortcode-code">
                                <?php echo esc_html($shortcode['shortcode']); ?>
                                <?php if (!empty($shortcode['attributes'])) : ?>
                                    <br><span style="color: #646970; font-size: 11px; display: block; margin-top: 5px;">
                                        <?php echo esc_html($shortcode['attributes']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <button class="polar-copy-btn" data-shortcode="<?php echo esc_attr($shortcode['shortcode']); ?>">
                                <?php esc_html_e('Copy Shortcode', 'pexpress'); ?>
                            </button>
                            <p style="margin-top: 10px; color: #646970; font-size: 13px;">
                                <strong><?php esc_html_e('Suggested Page Slug:', 'pexpress'); ?></strong>
                                <code><?php echo esc_html($shortcode['page_suggestion']); ?></code>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="polar-setup-section">
                <h2><?php esc_html_e('⚙️ Step 3: Configure SMS Settings (Optional)', 'pexpress'); ?></h2>
                <div class="polar-setup-step">
                    <h3><?php esc_html_e('SMS Notifications', 'pexpress'); ?></h3>
                    <p><?php esc_html_e('Configure SMS notifications to automatically notify team members when orders are assigned to them.', 'pexpress'); ?></p>
                    <ol>
                        <li><?php esc_html_e('Go to Polar Express → Settings', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Scroll to "SMS Configuration" section', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Enable SMS notifications', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Enter your SSLCommerz SMS API credentials', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Save settings', 'pexpress'); ?></li>
                    </ol>
                    <div class="polar-info-box">
                        <strong><?php esc_html_e('Note:', 'pexpress'); ?></strong> <?php esc_html_e('SMS notifications are optional. The plugin will work without SMS configuration, but team members won\'t receive automatic notifications.', 'pexpress'); ?>
                    </div>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-settings')); ?>" class="button button-primary">
                            <?php esc_html_e('Go to Settings →', 'pexpress'); ?>
                        </a>
                    </p>
                </div>
            </div>

            <div class="polar-setup-section">
                <h2><?php esc_html_e('🔄 Step 4: Workflow Overview', 'pexpress'); ?></h2>
                <div class="polar-setup-step">
                    <h3><?php esc_html_e('Order Processing Flow', 'pexpress'); ?></h3>
                    <ol>
                        <li><strong><?php esc_html_e('Order Received:', 'pexpress'); ?></strong> <?php esc_html_e('New orders appear in Agency Dashboard for assignment', 'pexpress'); ?></li>
                        <li><strong><?php esc_html_e('Agency Assignment:', 'pexpress'); ?></strong> <?php esc_html_e('Agency assigns orders to SR (Sales Representative), Fridge Provider, and Product Provider', 'pexpress'); ?></li>
                        <li><strong><?php esc_html_e('Product Provider Fulfills:', 'pexpress'); ?></strong> <?php esc_html_e('Product Provider marks orders as fulfilled', 'pexpress'); ?></li>
                        <li><strong><?php esc_html_e('Fridge Collection:', 'pexpress'); ?></strong> <?php esc_html_e('Fridge Provider collects and later returns the fridge', 'pexpress'); ?></li>
                        <li><strong><?php esc_html_e('SR Delivery:', 'pexpress'); ?></strong> <?php esc_html_e('SR marks orders as out for delivery and then delivered', 'pexpress'); ?></li>
                        <li><strong><?php esc_html_e('Customer Tracking:', 'pexpress'); ?></strong> <?php esc_html_e('Customers can track their orders in real-time using the Order Tracking page', 'pexpress'); ?></li>
                        <li><strong><?php esc_html_e('Support:', 'pexpress'); ?></strong> <?php esc_html_e('Support team can view all orders to assist customers', 'pexpress'); ?></li>
                    </ol>
                </div>
            </div>

            <div class="polar-setup-section">
                <h2><?php esc_html_e('💡 Tips & Best Practices', 'pexpress'); ?></h2>
                <div class="polar-setup-step">
                    <ul>
                        <li><?php esc_html_e('Create separate pages for each dashboard type for better organization', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Use descriptive page slugs (e.g., /agency-dashboard, /sr-dashboard, /order-tracking)', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Use the new shortcode names: [polar_agency], [polar_sr], [polar_product_provider] for clarity', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Create customer pages with [polar_order_tracking] and [polar_order_information] shortcodes', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Ensure all team members have WordPress user accounts before assigning roles', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Test the workflow with a test order before going live', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Keep SMS credentials secure and never share them publicly', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Regularly check the Agency Dashboard for pending assignments', 'pexpress'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="polar-setup-section">
                <h2><?php esc_html_e('❓ Need Help?', 'pexpress'); ?></h2>
                <div class="polar-setup-step">
                    <p><?php esc_html_e('If you encounter any issues or need assistance:', 'pexpress'); ?></p>
                    <ul>
                        <li><?php esc_html_e('Check the plugin documentation', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Review the error messages in WordPress admin notices', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Ensure WooCommerce is installed and active', 'pexpress'); ?></li>
                        <li><?php esc_html_e('Verify user roles are correctly assigned', 'pexpress'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.polar-copy-btn').on('click', function() {
                    var $btn = $(this);
                    var shortcode = $btn.data('shortcode');
                    var $temp = $('<textarea>');
                    $('body').append($temp);
                    $temp.val(shortcode).select();
                    document.execCommand('copy');
                    $temp.remove();

                    var originalText = $btn.text();
                    $btn.text('<?php echo esc_js(__('Copied!', 'pexpress')); ?>').addClass('copied');
                    setTimeout(function() {
                        $btn.text(originalText).removeClass('copied');
                    }, 2000);
                });
            });
        </script>
    <?php
    }

    /**
     * Render Changelog page
     */
    public function render_changelog_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        $changelog = array(
            '1.0.5' => array(
                'date' => date('Y-m-d'),
                'added' => array(
                    __('Redesigned customer order tracking with horizontal progress bar', 'pexpress'),
                    __('Click navigation to order detail pages from tracking cards', 'pexpress'),
                    __('Clean minimal design for order tracking interface', 'pexpress'),
                ),
                'improved' => array(
                    __('Customer order tracking UI with single-layer progress visualization', 'pexpress'),
                    __('Mobile responsive design for order tracking cards', 'pexpress'),
                    __('Search and filter layout to prevent overlapping elements', 'pexpress'),
                    __('Progress bar with milestone labels directly under each stage', 'pexpress'),
                ),
                'fixed' => array(
                    __('Fixed search box and filter dropdown overlapping issue', 'pexpress'),
                    __('Removed duplicate progress visualization layers', 'pexpress'),
                    __('Improved mobile responsiveness for order tracking interface', 'pexpress'),
                ),
            ),
            '1.0.4' => array(
                'date' => '2024-12-19',
                'added' => array(
                    __('Role-based user management system with role table view', 'pexpress'),
                    __('Multi-select user assignment modal with intelligent filtering', 'pexpress'),
                    __('Setup Guideline page with comprehensive documentation', 'pexpress'),
                    __('Enhanced shortcodes for all dashboard types with proper data preparation', 'pexpress'),
                    __('User role filtering to prevent duplicate role assignments', 'pexpress'),
                    __('Support for assigning multiple roles to users', 'pexpress'),
                    __('Improved modal UI with full-width selection boxes', 'pexpress'),
                    __('Inline user removal with visual feedback', 'pexpress'),
                    __('Changelog page for version tracking', 'pexpress'),
                ),
                'improved' => array(
                    __('Settings page UI with role-centric design', 'pexpress'),
                    __('User assignment workflow with better UX', 'pexpress'),
                    __('Modal responsiveness and usability', 'pexpress'),
                    __('Table display showing all user roles at a glance', 'pexpress'),
                ),
                'fixed' => array(
                    __('Shortcode data preparation for frontend pages', 'pexpress'),
                    __('Role assignment validation and error handling', 'pexpress'),
                ),
            ),
            '1.0.3' => array(
                'date' => '2024-12-15',
                'added' => array(
                    __('Real-time order updates via WordPress Heartbeat API', 'pexpress'),
                    __('SMS notification system integration with SSLCommerz', 'pexpress'),
                    __('Custom order statuses for Polar Express workflow', 'pexpress'),
                    __('AJAX-powered order assignment system', 'pexpress'),
                ),
                'improved' => array(
                    __('Dashboard performance and loading times', 'pexpress'),
                    __('Order status update mechanisms', 'pexpress'),
                ),
            ),
            '1.0.2' => array(
                'date' => '2024-12-10',
                'added' => array(
                    __('Support dashboard for customer service team', 'pexpress'),
                    __('Distributor dashboard for order fulfillment tracking', 'pexpress'),
                    __('Fridge provider dashboard for rental management', 'pexpress'),
                    __('Delivery dashboard for delivery personnel', 'pexpress'),
                ),
                'improved' => array(
                    __('Dashboard templates with mobile-responsive design', 'pexpress'),
                    __('Order filtering and status management', 'pexpress'),
                ),
            ),
            '1.0.1' => array(
                'date' => '2024-12-05',
                'added' => array(
                    __('HR dashboard for order assignment', 'pexpress'),
                    __('Role-based access control system', 'pexpress'),
                    __('WooCommerce order integration', 'pexpress'),
                ),
                'improved' => array(
                    __('Plugin initialization and dependency management', 'pexpress'),
                    __('Security with nonce verification and capability checks', 'pexpress'),
                ),
            ),
            '1.0.0' => array(
                'date' => '2024-12-01',
                'added' => array(
                    __('Initial release of Polar Express plugin', 'pexpress'),
                    __('Custom WordPress roles: Polar HR, Polar Delivery, Polar Fridge Provider, Polar Distributor, Polar Support', 'pexpress'),
                    __('WooCommerce integration for order management', 'pexpress'),
                    __('Plugin architecture and core functionality', 'pexpress'),
                    __('Admin interface foundation', 'pexpress'),
                ),
            ),
        );

        $this->render_changelog_html($changelog);
    }

    /**
     * Render Test Mail page
     */
    public function render_test_mail_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'pexpress'));
        }

        // Get current email configuration
        $options = get_option('pexpress_options', array());
        $email_config = isset($options['email_config']) ? $options['email_config'] : array();
        $mailgun_config = isset($options['mailgun_config']) ? $options['mailgun_config'] : array();

        $email_enabled = !empty($email_config['enable_email']);
        $mailgun_enabled = !empty($mailgun_config['enable_mailgun']);
        $from_name = isset($email_config['from_name']) ? $email_config['from_name'] : get_bloginfo('name');
        $from_email = isset($email_config['from_email']) ? $email_config['from_email'] : get_option('admin_email');
        $mailgun_configured = !empty($mailgun_config['api_key']) && !empty($mailgun_config['domain']);

        // SMS config
        $sms_config = isset($options['sms_config']) ? $options['sms_config'] : array();
        $sms_enabled = !empty($sms_config['enable_plugin']);
        $sms_api_token = isset($sms_config['api_hash_token']) ? $sms_config['api_hash_token'] : '';
        $sms_api_sid = isset($sms_config['api_sid']) ? $sms_config['api_sid'] : '';
        $sms_configured = !empty($sms_api_token) && !empty($sms_api_sid);

        $this->render_test_mail_html($email_enabled, $mailgun_enabled, $from_name, $from_email, $mailgun_configured, $sms_enabled, $sms_configured, $sms_api_sid);
    }

    /**
     * Render Test Mail HTML
     */
    private function render_test_mail_html($email_enabled, $mailgun_enabled, $from_name, $from_email, $mailgun_configured, $sms_enabled = false, $sms_configured = false, $sms_api_sid = '')
    {
    ?>
        <div class="wrap pexpress-test-mail">
            <h1><?php esc_html_e('Test Email & SMS', 'pexpress'); ?></h1>
            <p><?php esc_html_e('Send a test email or SMS to verify your configuration is working correctly.', 'pexpress'); ?></p>

            <div class="pexpress-test-mail-status" style="margin: 20px 0; padding: 15px; background: #fff; border-left: 4px solid #2271b1;">
                <h3 style="margin-top: 0;"><?php esc_html_e('Email Configuration Status', 'pexpress'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Email Notifications:', 'pexpress'); ?></th>
                        <td>
                            <?php if ($email_enabled) : ?>
                                <span style="color: #46b450;">✓ <?php esc_html_e('Enabled', 'pexpress'); ?></span>
                            <?php else : ?>
                                <span style="color: #dc3232;">✗ <?php esc_html_e('Disabled', 'pexpress'); ?></span>
                                <p class="description"><?php esc_html_e('Enable email notifications in Settings → Email Configuration', 'pexpress'); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('From Name:', 'pexpress'); ?></th>
                        <td><?php echo esc_html($from_name); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('From Email:', 'pexpress'); ?></th>
                        <td><?php echo esc_html($from_email); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Mailgun:', 'pexpress'); ?></th>
                        <td>
                            <?php if ($mailgun_enabled && $mailgun_configured) : ?>
                                <span style="color: #46b450;">✓ <?php esc_html_e('Enabled & Configured', 'pexpress'); ?></span>
                            <?php elseif ($mailgun_enabled && !$mailgun_configured) : ?>
                                <span style="color: #dc3232;">✗ <?php esc_html_e('Enabled but not configured', 'pexpress'); ?></span>
                                <p class="description"><?php esc_html_e('Configure Mailgun API key and domain in Settings', 'pexpress'); ?></p>
                            <?php else : ?>
                                <span style="color: #646970;">— <?php esc_html_e('Disabled (using wp_mail)', 'pexpress'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="pexpress-test-mail-form" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2><?php esc_html_e('Send Test Email', 'pexpress'); ?></h2>
                <form id="pexpress-test-mail-form">
                    <?php wp_nonce_field('pexpress_test_mail', 'pexpress_test_mail_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="test_email_to"><?php esc_html_e('Recipient Email', 'pexpress'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="test_email_to" name="test_email_to" value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" required />
                                <p class="description"><?php esc_html_e('Enter the email address where you want to receive the test email', 'pexpress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="test_email_subject"><?php esc_html_e('Subject', 'pexpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="test_email_subject" name="test_email_subject" value="<?php echo esc_attr(sprintf(__('Test Email from %s', 'pexpress'), get_bloginfo('name'))); ?>" class="regular-text" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="test_email_message"><?php esc_html_e('Message', 'pexpress'); ?></label>
                            </th>
                            <td>
                                <textarea id="test_email_message" name="test_email_message" rows="10" class="large-text" required><?php echo esc_textarea(__('This is a test email from Polar Express plugin.

If you received this email, your email configuration is working correctly!

Email Method: {{method}}
Sent At: {{time}}
Site: {{site}}', 'pexpress')); ?></textarea>
                                <p class="description"><?php esc_html_e('You can customize the test message. Placeholders: {{method}}, {{time}}, {{site}}', 'pexpress'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="pexpress-send-test-email">
                            <span class="dashicons dashicons-email-alt" style="vertical-align: middle; margin-right: 5px;"></span>
                            <?php esc_html_e('Send Test Email', 'pexpress'); ?>
                        </button>
                    </p>
                </form>

                <div id="pexpress-test-mail-result" style="display: none; margin-top: 20px; padding: 15px; border-radius: 4px;"></div>
            </div>

            <div class="pexpress-test-mail-info" style="background: #f6f7f7; padding: 15px; margin: 20px 0; border-left: 4px solid #2271b1;">
                <h3 style="margin-top: 0;"><?php esc_html_e('Email Tips', 'pexpress'); ?></h3>
                <ul>
                    <li><?php esc_html_e('Check your spam folder if you don\'t receive the email', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Verify your email configuration in Settings → Email Configuration', 'pexpress'); ?></li>
                    <li><?php esc_html_e('If using Mailgun, ensure your domain is verified', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Check the Email Log page to see detailed sending information', 'pexpress'); ?></li>
                </ul>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-settings&tab=email')); ?>" class="button">
                        <?php esc_html_e('Go to Email Settings →', 'pexpress'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-email-log')); ?>" class="button">
                        <?php esc_html_e('View Email Log →', 'pexpress'); ?>
                    </a>
                </p>
            </div>

            <!-- SMS Testing Section -->
            <hr style="margin: 40px 0; border: none; border-top: 2px solid #ccd0d4;" />

            <div class="pexpress-test-sms-status" style="margin: 20px 0; padding: 15px; background: #fff; border-left: 4px solid #00a32a;">
                <h3 style="margin-top: 0;"><?php esc_html_e('SMS Configuration Status', 'pexpress'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('SMS Notifications:', 'pexpress'); ?></th>
                        <td>
                            <?php if ($sms_enabled) : ?>
                                <span style="color: #46b450;">✓ <?php esc_html_e('Enabled', 'pexpress'); ?></span>
                            <?php else : ?>
                                <span style="color: #dc3232;">✗ <?php esc_html_e('Disabled', 'pexpress'); ?></span>
                                <p class="description"><?php esc_html_e('Enable SMS notifications in Settings → SMS Configuration', 'pexpress'); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('API Token:', 'pexpress'); ?></th>
                        <td>
                            <?php if ($sms_configured) : ?>
                                <span style="color: #46b450;">✓ <?php esc_html_e('Configured', 'pexpress'); ?></span>
                            <?php else : ?>
                                <span style="color: #dc3232;">✗ <?php esc_html_e('Not configured', 'pexpress'); ?></span>
                                <p class="description"><?php esc_html_e('Set your API Token in Settings → SMS Configuration', 'pexpress'); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('SID/Stakeholder:', 'pexpress'); ?></th>
                        <td>
                            <?php if (!empty($sms_api_sid)) : ?>
                                <?php echo esc_html($sms_api_sid); ?>
                            <?php else : ?>
                                <span style="color: #dc3232;">✗ <?php esc_html_e('Not set', 'pexpress'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="pexpress-test-sms-form" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2><?php esc_html_e('Send Test SMS', 'pexpress'); ?></h2>
                <form id="pexpress-test-sms-form">
                    <?php wp_nonce_field('pexpress_test_sms', 'pexpress_test_sms_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="test_sms_phone"><?php esc_html_e('Phone Number', 'pexpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="test_sms_phone" name="test_sms_phone" value="" placeholder="01XXXXXXXXX" class="regular-text" required />
                                <p class="description"><?php esc_html_e('Enter the phone number where you want to receive the test SMS (Bangladesh format: 01XXXXXXXXX)', 'pexpress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="test_sms_message"><?php esc_html_e('Message', 'pexpress'); ?></label>
                            </th>
                            <td>
                                <textarea id="test_sms_message" name="test_sms_message" rows="4" class="large-text" required><?php echo esc_textarea(__('This is a test SMS from Polar Express plugin. If you received this, your SMS configuration is working! Sent at: {{time}}', 'pexpress')); ?></textarea>
                                <p class="description"><?php esc_html_e('You can customize the test message. Placeholders: {{time}}, {{site}}', 'pexpress'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="pexpress-send-test-sms" style="background: #00a32a; border-color: #00a32a;">
                            <span class="dashicons dashicons-smartphone" style="vertical-align: middle; margin-right: 5px;"></span>
                            <?php esc_html_e('Send Test SMS', 'pexpress'); ?>
                        </button>
                    </p>
                </form>

                <div id="pexpress-test-sms-result" style="display: none; margin-top: 20px; padding: 15px; border-radius: 4px;"></div>
            </div>

            <div class="pexpress-test-sms-info" style="background: #f6f7f7; padding: 15px; margin: 20px 0; border-left: 4px solid #00a32a;">
                <h3 style="margin-top: 0;"><?php esc_html_e('SMS Tips', 'pexpress'); ?></h3>
                <ul>
                    <li><?php esc_html_e('Make sure your API Token and SID are correctly configured', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Phone number should be in Bangladesh format (01XXXXXXXXX)', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Check that your SMS account has sufficient balance', 'pexpress'); ?></li>
                    <li><?php esc_html_e('Enable WP_DEBUG to see detailed SMS logs in debug.log', 'pexpress'); ?></li>
                </ul>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-settings&tab=sms')); ?>" class="button">
                        <?php esc_html_e('Go to SMS Settings →', 'pexpress'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=polar-express-settings&tab=templates')); ?>" class="button">
                        <?php esc_html_e('SMS Templates →', 'pexpress'); ?>
                    </a>
                </p>
            </div>
        </div>

        <style>
            .pexpress-test-mail .form-table th {
                width: 200px;
            }

            #pexpress-test-mail-result.success {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
            }

            #pexpress-test-mail-result.error {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }

            #pexpress-test-mail-result.info {
                background: #d1ecf1;
                border: 1px solid #bee5eb;
                color: #0c5460;
            }

            #pexpress-send-test-email:disabled,
            #pexpress-send-test-sms:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            #pexpress-test-sms-result.success {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
            }

            #pexpress-test-sms-result.error {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }

            #pexpress-test-sms-result.info {
                background: #d1ecf1;
                border: 1px solid #bee5eb;
                color: #0c5460;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                $('#pexpress-test-mail-form').on('submit', function(e) {
                    e.preventDefault();

                    var $form = $(this);
                    var $button = $('#pexpress-send-test-email');
                    var $result = $('#pexpress-test-mail-result');
                    var originalText = $button.html();

                    // Disable button and show loading
                    $button.prop('disabled', true);
                    $button.html('<span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span> <?php echo esc_js(__('Sending...', 'pexpress')); ?>');
                    $result.hide();

                    // Get form data
                    var formData = {
                        action: 'pexpress_send_test_email',
                        nonce: '<?php echo wp_create_nonce('pexpress_test_mail_ajax'); ?>',
                        to: $('#test_email_to').val(),
                        subject: $('#test_email_subject').val(),
                        message: $('#test_email_message').val()
                    };

                    // Send AJAX request
                    $.ajax({
                        url: (typeof ajaxurl !== 'undefined' ? ajaxurl : (typeof polarExpress !== 'undefined' ? polarExpress.ajaxUrl : '<?php echo admin_url('admin-ajax.php'); ?>')),
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            $button.prop('disabled', false);
                            $button.html(originalText);

                            if (response.success) {
                                $result.removeClass('error info').addClass('success').html(
                                    '<strong><?php echo esc_js(__('Success!', 'pexpress')); ?></strong> ' +
                                    response.data.message
                                ).show();
                            } else {
                                $result.removeClass('success info').addClass('error').html(
                                    '<strong><?php echo esc_js(__('Error:', 'pexpress')); ?></strong> ' +
                                    (response.data && response.data.message ? response.data.message : '<?php echo esc_js(__('Unknown error occurred', 'pexpress')); ?>')
                                ).show();
                            }
                        },
                        error: function(xhr, status, error) {
                            $button.prop('disabled', false);
                            $button.html(originalText);
                            $result.removeClass('success info').addClass('error').html(
                                '<strong><?php echo esc_js(__('Error:', 'pexpress')); ?></strong> ' +
                                '<?php echo esc_js(__('Failed to send request. Please try again.', 'pexpress')); ?>'
                            ).show();
                        }
                    });
                });

                // SMS Test Form
                $('#pexpress-test-sms-form').on('submit', function(e) {
                    e.preventDefault();

                    var $form = $(this);
                    var $button = $('#pexpress-send-test-sms');
                    var $result = $('#pexpress-test-sms-result');
                    var originalText = $button.html();

                    // Disable button and show loading
                    $button.prop('disabled', true);
                    $button.html('<span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span> <?php echo esc_js(__('Sending...', 'pexpress')); ?>');
                    $result.hide();

                    // Get form data
                    var formData = {
                        action: 'pexpress_send_test_sms',
                        nonce: '<?php echo wp_create_nonce('pexpress_test_sms_ajax'); ?>',
                        phone: $('#test_sms_phone').val(),
                        message: $('#test_sms_message').val()
                    };

                    // Send AJAX request
                    $.ajax({
                        url: (typeof ajaxurl !== 'undefined' ? ajaxurl : (typeof polarExpress !== 'undefined' ? polarExpress.ajaxUrl : '<?php echo admin_url('admin-ajax.php'); ?>')),
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            $button.prop('disabled', false);
                            $button.html(originalText);

                            if (response.success) {
                                $result.removeClass('error info').addClass('success').html(
                                    '<strong><?php echo esc_js(__('Success!', 'pexpress')); ?></strong> ' +
                                    response.data.message
                                ).show();
                            } else {
                                $result.removeClass('success info').addClass('error').html(
                                    '<strong><?php echo esc_js(__('Error:', 'pexpress')); ?></strong> ' +
                                    (response.data && response.data.message ? response.data.message : '<?php echo esc_js(__('Unknown error occurred', 'pexpress')); ?>')
                                ).show();
                            }
                        },
                        error: function(xhr, status, error) {
                            $button.prop('disabled', false);
                            $button.html(originalText);
                            $result.removeClass('success info').addClass('error').html(
                                '<strong><?php echo esc_js(__('Error:', 'pexpress')); ?></strong> ' +
                                '<?php echo esc_js(__('Failed to send request. Please try again.', 'pexpress')); ?>'
                            ).show();
                        }
                    });
                });
            });
        </script>
    <?php
    }

    /**
     * Render Changelog HTML
     */
    private function render_changelog_html($changelog)
    {
    ?>
        <div class="wrap polar-changelog">
            <div class="polar-changelog-header">
                <h1><?php esc_html_e('Polar Express Changelog', 'pexpress'); ?></h1>
                <p><?php esc_html_e('Complete version history and release notes for Polar Express plugin', 'pexpress'); ?></p>
            </div>

            <?php foreach ($changelog as $version => $details) : ?>
                <div class="polar-version-section">
                    <div class="polar-version-header">
                        <h2>
                            <?php echo esc_html($version); ?>
                            <?php if ($version === '1.0.5') : ?>
                                <span class="polar-badge latest"><?php esc_html_e('Latest', 'pexpress'); ?></span>
                            <?php endif; ?>
                        </h2>
                        <span class="polar-version-date"><?php echo esc_html($details['date']); ?></span>
                    </div>

                    <?php if (!empty($details['added'])) : ?>
                        <div class="polar-changelog-category added">
                            <h3>
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php esc_html_e('Added', 'pexpress'); ?>
                            </h3>
                            <ul>
                                <?php foreach ($details['added'] as $item) : ?>
                                    <li><?php echo esc_html($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($details['improved'])) : ?>
                        <div class="polar-changelog-category improved">
                            <h3>
                                <span class="dashicons dashicons-arrow-up-alt"></span>
                                <?php esc_html_e('Improved', 'pexpress'); ?>
                            </h3>
                            <ul>
                                <?php foreach ($details['improved'] as $item) : ?>
                                    <li><?php echo esc_html($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($details['fixed'])) : ?>
                        <div class="polar-changelog-category fixed">
                            <h3>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Fixed', 'pexpress'); ?>
                            </h3>
                            <ul>
                                <?php foreach ($details['fixed'] as $item) : ?>
                                    <li><?php echo esc_html($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="polar-version-section" style="background: #f6f7f7; border-left: 4px solid #2271b1;">
                <h3 style="margin-top: 0; color: #1d2327;"><?php esc_html_e('About Polar Express', 'pexpress'); ?></h3>
                <p style="color: #646970; line-height: 1.6;">
                    <?php esc_html_e('Polar Express is a custom WordPress extension designed to enhance manual order processing and delivery workflows for Polar\'s bulk ice cream service. The plugin provides role-based dashboards, real-time task assignments, order management, and tracking capabilities for all stakeholders involved in the order fulfillment process.', 'pexpress'); ?>
                </p>
                <p style="color: #646970; line-height: 1.6; margin-top: 15px;">
                    <strong><?php esc_html_e('Plugin Version:', 'pexpress'); ?></strong> <?php echo esc_html(PEXPRESS_VERSION); ?><br>
                    <strong><?php esc_html_e('WooCommerce Compatibility:', 'pexpress'); ?></strong> 8.0+<br>
                    <strong><?php esc_html_e('WordPress Compatibility:', 'pexpress'); ?></strong> 6.0+<br>
                    <strong><?php esc_html_e('PHP Requirement:', 'pexpress'); ?></strong> 7.4+
                </p>
            </div>
        </div>
<?php
    }
}
