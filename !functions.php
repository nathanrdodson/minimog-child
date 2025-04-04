<?php
/**     Add Imok Styles      */
define('IMOK_ASSETS_VERSION', '1.0.18');
function imok_style()
{
    wp_enqueue_style('imokcss', get_stylesheet_directory_uri() . '/scss/custom.css', array(), IMOK_ASSETS_VERSION);
    wp_enqueue_script('imokjs', get_stylesheet_directory_uri() . '/imok.js', array(), IMOK_ASSETS_VERSION);
}
add_action('wp_enqueue_scripts', 'imok_style', 99);

function custom_disable_plugin_updates_and_display($value)
{
    $pluginsToDisableUpdates = [
        'payment-gateway-stripe-and-woocommerce-integration/payment-gateway-stripe-and-woocommerce-integration.php',
        'koala-order-chat-for-woocommerce/class-af-communication-main.php',
    ];

    if (isset($value) && is_object($value)) {
        foreach ($pluginsToDisableUpdates as $plugin) {
            if (isset($value->response[$plugin])) {
                unset($value->response[$plugin]);
            }
        }
    }

    add_filter('plugin_auto_update_setting_html', function ($html, $plugin_file) use ($pluginsToDisableUpdates) {
        if (in_array($plugin_file, $pluginsToDisableUpdates, true)) {
            $html = '<span style="color:red;">Auto-Updates DISABLED</span>';
        }

        return $html;
    }, 10, 2);

    return $value;
}
add_filter('site_transient_update_plugins', 'custom_disable_plugin_updates_and_display', 10, 1);

//// Hook into the order creation process

function check_order_products($order_id)
{
    $order = wc_get_order($order_id);

    $all_items_in_lids_category = false;

    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();

        if (has_term('cups', 'product_cat', $product_id)) {
            $all_items_in_lids_category = true;
            break;
        }
    }

    if (!$all_items_in_lids_category) {
        update_field('artwork_status', 'approved', $order_id);
    }
}
add_action('woocommerce_order_status_on-hold', 'check_order_products');

function add_reseller_role() {
    $wp_roles = wp_roles();

    $customerRole = $wp_roles->get_role( 'customer' ); // Copy customer role capabilities

    $role = 'reseller';
    $display_name = 'Reseller';
    add_role( $role , $display_name , $customerRole->capabilities );
}
add_action('init', 'add_reseller_role');

function zero_rate_for_custom_user_role( $tax_class, $product ) {
    // Getting the current user
    $current_user = wp_get_current_user();
    $current_user_data = get_userdata($current_user->ID);

    //  <== <== <== <== <== <== <== Here you put your user role slug
    if ( in_array( 'resellers', $current_user_data->roles ) )
        $tax_class = 'Zero Rate';

    return $tax_class;
}
add_filter( 'woocommerce_product_get_tax_class', 'wc_diff_rate_for_user', 10, 2 );
add_filter( 'woocommerce_product_variation_get_tax_class', 'wc_diff_rate_for_user', 10, 2 );
