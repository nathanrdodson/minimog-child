<?php
/**
 * My Account Dashboard
 *
 * Shows the dashboard section of the My Account page.
 *
 * This template overrides the default WooCommerce dashboard template.
 * 
 * @package Minimog Child
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$current_user = wp_get_current_user();
$customer = new WC_Customer(get_current_user_id());

// Get recent orders
$args = array(
    'customer_id' => get_current_user_id(),
    'limit' => 5,
    'status' => array('completed', 'processing', 'on-hold'),
);
$recent_orders = wc_get_orders($args);
$recent_order_count = count($recent_orders);

// Get the user's default shipping address
$shipping_address = $customer->get_shipping();
$has_shipping_address = !empty($shipping_address['address_1']);

// Get downloadable products count
$downloads = WC()->customer->get_downloadable_products();
$download_count = count($downloads);
?>

<div class="minimog-dashboard">
    <p class="dashboard-welcome">
        <?php
        printf(
            /* translators: %1$s: User display name. */
            esc_html__(
                'Hello %1$s (not %1$s? %2$sLog out%3$s)',
                'minimog-child'
            ),
            '<strong>' . esc_html($current_user->display_name) . '</strong>',
            '<a href="' . esc_url(wc_logout_url()) . '">',
            '</a>'
        );
        ?>
    </p>

    <div class="dashboard-cards">
        <div class="dashboard-card">
            <h3><?php esc_html_e('Recent Orders', 'minimog-child'); ?></h3>
            <div class="card-content">
                <p>
                    <?php 
                    printf(
                        /* translators: %d: number of orders */
                        _n(
                            'You have %d recent order',
                            'You have %d recent orders',
                            $recent_order_count,
                            'minimog-child'
                        ),
                        $recent_order_count
                    ); 
                    ?>
                </p>
                <?php if ($recent_order_count > 0) : ?>
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="view-link">
                        <?php esc_html_e('View >', 'minimog-child'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard-card">
            <h3><?php esc_html_e('Shipping Address', 'minimog-child'); ?></h3>
            <div class="card-content">
                <?php if ($has_shipping_address) : ?>
                    <p>
                        <?php echo wp_kses_post($customer->get_formatted_shipping_address()); ?>
                    </p>
                <?php else : ?>
                    <p><?php esc_html_e('You have not set up a shipping address yet.', 'minimog-child'); ?></p>
                <?php endif; ?>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="view-link">
                    <?php esc_html_e('Edit >', 'minimog-child'); ?>
                </a>
            </div>
        </div>
        
        <div class="dashboard-card">
            <h3><?php esc_html_e('Account Details', 'minimog-child'); ?></h3>
            <div class="card-content">
                <p><?php esc_html_e('Update your profile and preferences', 'minimog-child'); ?></p>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="view-link">
                    <?php esc_html_e('Edit >', 'minimog-child'); ?>
                </a>
            </div>
        </div>
        
        <?php if ($download_count > 0) : ?>
        <div class="dashboard-card">
            <h3><?php esc_html_e('Downloads', 'minimog-child'); ?></h3>
            <div class="card-content">
                <p>
                    <?php 
                    printf(
                        /* translators: %d: number of downloads */
                        _n(
                            'You have %d downloadable product',
                            'You have %d downloadable products',
                            $download_count,
                            'minimog-child'
                        ),
                        $download_count
                    ); 
                    ?>
                </p>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('downloads')); ?>" class="view-link">
                    <?php esc_html_e('View >', 'minimog-child'); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($recent_order_count > 0) : ?>
    <div class="recent-activity">
        <h3><?php esc_html_e('Recent Activity', 'minimog-child'); ?></h3>
        <ul class="activity-list">
            <?php foreach ($recent_orders as $order) : ?>
                <li class="activity-item">
                    <?php
                    printf(
                        /* translators: %1$s: order number, %2$s: order date */
                        esc_html__('Order #%1$s was placed on %2$s', 'minimog-child'),
                        '<a href="' . esc_url($order->get_view_order_url()) . '">' . esc_html($order->get_order_number()) . '</a>',
                        esc_html(wc_format_datetime($order->get_date_created()))
                    );
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<?php
/**
 * My Account dashboard
 * Always keep this hook to maintain compatibility with WooCommerce
 */
do_action('woocommerce_account_dashboard');
?>
