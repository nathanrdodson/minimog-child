<?php
/**
 * Minimog Child Theme Functions
 *
 * @package Minimog Child
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Enqueue parent theme styles and child theme styles
 */
function minimog_child_enqueue_styles() {
    // Parent theme style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    // Child theme style
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'minimog_child_enqueue_styles');

/**
 * Include custom My Account functions
 */
require_once get_stylesheet_directory() . '/includes/woocommerce-myaccount-functions.php';

/**
 * Set up custom folder structure for the child theme
 */
function minimog_child_setup() {
    // Create necessary directories if they don't exist
    $directories = array(
        '/assets/css',
        '/assets/js',
        '/assets/images',
        '/woocommerce/myaccount',
        '/includes'
    );
    
    foreach ($directories as $directory) {
        $dir_path = get_stylesheet_directory() . $directory;
        if (!file_exists($dir_path)) {
            wp_mkdir_p($dir_path);
        }
    }
    
    // Add theme support for WooCommerce
    add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'minimog_child_setup');

/**
 * Add a custom endpoint for Profile Photo in My Account section
 * This function is declared here to ensure it runs on theme activation
 */
function minimog_child_add_endpoints() {
    add_rewrite_endpoint('profile-photo', EP_ROOT | EP_PAGES);
    
    // Flush rewrite rules only once
    if (!get_option('minimog_child_flushed_rewrite_rules')) {
        flush_rewrite_rules();
        update_option('minimog_child_flushed_rewrite_rules', true);
    }
}
add_action('init', 'minimog_child_add_endpoints');

/**
 * Register activation hook to flush rewrite rules when theme is activated
 */
function minimog_child_theme_activation() {
    minimog_child_add_endpoints();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'minimog_child_theme_activation');



function add_view_item_link_to_footer() {
    if (is_product()) {
        global $post;

        $product_id = $post->ID;
        $product_name = get_the_title($product_id);
        $product_sku = get_post_meta($product_id, '_sku', true);
        $product_price = get_post_meta($product_id, '_price', true);

        ?>
        <script>
            gtag('event', 'view_item', {
                items: [{
                    id: '<?php echo esc_js($product_id); ?>',
                    name: '<?php echo esc_js($product_name); ?>',
                    sku: '<?php echo esc_js($product_sku); ?>',
                    price: <?php echo esc_js($product_price); ?>,
                }],
            });
        </script>
        <?php
    }
}

add_action('wp_footer', 'add_view_item_link_to_footer');


function live_chat()
{
    ?>
    <script>

        window.__lc = window.__lc || {};

        window.__lc.license = 16914771;

        ;(function (n, t, c) {
            function i(n) {
                return e._h ? e._h.apply(null, n) : e._q.push(n)
            }

            var e = {
                _q: [], _h: null, _v: "2.0", on: function () {
                    i(["on", c.call(arguments)])
                }, once: function () {
                    i(["once", c.call(arguments)])
                }, off: function () {
                    i(["off", c.call(arguments)])
                }, get: function () {
                    if (!e._h) throw new Error("[LiveChatWidget] You can't use getters before load.");
                    return i(["get", c.call(arguments)])
                }, call: function () {
                    i(["call", c.call(arguments)])
                }, init: function () {
                    var n = t.createElement("script");
                    n.async = !0, n.type = "text/javascript", n.src = "https://cdn.livechatinc.com/tracking.js", t.head.appendChild(n)
                }
            };
            !n.__lc.asyncInit && e.init(), n.LiveChatWidget = n.LiveChatWidget || e
        }(window, document, [].slice))

    </script>

    <noscript><a href="https://www.livechat.com/chat-with/16914771/" rel="nofollow">Chat with us</a>, powered by <a
                href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>
    <?php

}

add_action('wp_head', 'live_chat');


// Approved order status set based on order items (if no cups in order set to approved)
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

//Add reseller user role
function add_reseller_role()
{
    $wp_roles = wp_roles();

    $customerRole = $wp_roles->get_role('customer'); // Copy customer role capabilities

    $role = 'reseller';
    $display_name = 'Reseller';
    add_role($role, $display_name, $customerRole->capabilities);
}

//add_action('init', 'add_reseller_role');


//No tax for Reseller users
function wc_diff_rate_for_user($tax_class, $product)
{
    // Getting the current user
    $current_user = wp_get_current_user();
    $current_user_data = get_userdata($current_user->ID);

    if ($current_user_data) {
        if (in_array('reseller', $current_user_data->roles))
            $tax_class = 'Zero Rate';

        return $tax_class;
    }
}

add_filter('woocommerce_product_get_tax_class', 'wc_diff_rate_for_user', 10, 2);
add_filter('woocommerce_product_variation_get_tax_class', 'wc_diff_rate_for_user', 10, 2);

// Add custom order statuses
function add_custom_order_statuses()
{
    register_post_status('wc-pending-review', array(
        'label' => _x('Pending Review', 'Order status', 'woocommerce'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Pending Review <span class="count">(%s)</span>', 'Pending Review <span class="count">(%s)</span>', 'woocommerce'),
    ));

    register_post_status('wc-artwork-app-pendi', array(
        'label' => _x('Artwork Approval Pending', 'Order status', 'woocommerce'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Artwork Approval Pending <span class="count">(%s)</span>', 'Artwork Approval Pending <span class="count">(%s)</span>', 'woocommerce'),
    ));

    register_post_status('wc-order-approved', array(
        'label' => _x('Order Approved', 'Order status', 'woocommerce'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Order Approved <span class="count">(%s)</span>', 'Order Approved <span class="count">(%s)</span>', 'woocommerce'),
    ));

    register_post_status('wc-in-production', array(
        'label' => _x('In Production', 'Order status', 'woocommerce'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('In Production <span class="count">(%s)</span>', 'In Production <span class="count">(%s)</span>', 'woocommerce'),
    ));

    register_post_status('wc-shipped', array(
        'label' => _x('Shipped', 'Order status', 'woocommerce'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'woocommerce'),
    ));
}

add_action('init', 'add_custom_order_statuses');

// Display custom order statuses in order list
function custom_order_statuses($order_statuses)
{
    $order_statuses['wc-pending-review'] = _x('Pending Review', 'Order status', 'woocommerce');
    $order_statuses['wc-artwork-app-pendi'] = _x('Artwork Approval Pending', 'Order status', 'woocommerce');
    $order_statuses['wc-order-approved'] = _x('Order Approved', 'Order status', 'woocommerce');
    $order_statuses['wc-in-production'] = _x('In Production', 'Order status', 'woocommerce');
    $order_statuses['wc-shipped'] = _x('Shipped', 'Order status', 'woocommerce');
    return $order_statuses;
}

add_filter('wc_order_statuses', 'custom_order_statuses');

add_action('wp', function() {
    // Remove the tabs from ALL locations they might be added by the theme
    remove_action('woocommerce_after_single_product', 'woocommerce_output_product_data_tabs', 10);
    remove_action('woocommerce_single_product_summary', array(Minimog\Woo\Single_Product::instance(), 'output_product_data_tabs_as_toggles'), 100);
    remove_action('woocommerce_after_single_product', array(Minimog\Woo\Single_Product::instance(), 'output_discussion_tabs'), 10);
}, 100);

/**
 * Inksplosion - Cross-Sells Products Tab
 * 
 * Adds a tab that shows products defined in the Cross-sells section
 * of the Linked Products tab in the product editor
 */

// Add a new tab to the product tabs
function inksplosion_add_related_tab($tabs) {
    // Only add the tab if we're on a single product page
    if (!is_product()) {
        return $tabs;
    }
    
    // Add our new tab
    $tabs['inksplosion_related_tab'] = array(
        'title'    => __('Related Products', 'inksplosion'),
        'priority' => 25,
        'callback' => 'inksplosion_related_tab_content'
    );
    
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'inksplosion_add_related_tab');

/**
 * Display the content in the related products tab using cross-sells
 */
function inksplosion_related_tab_content() {
    global $product;
    
    if (!$product) {
        return;
    }
    
    // Get the cross-sells IDs
    $cross_sell_ids = $product->get_cross_sell_ids();
    
    // Limit to maximum 4 products
    $cross_sell_ids = array_slice($cross_sell_ids, 0, 4);
    
    if (empty($cross_sell_ids)) {
        echo '<p>' . __('No related products found.', 'inksplosion') . '</p>';
        return;
    }
    
    // Override related products with cross-sells
    add_filter('woocommerce_related_products', function($related_products) use ($cross_sell_ids) {
        return $cross_sell_ids;
    }, 100);
    
    // Use WooCommerce's native template for displaying related products
    woocommerce_output_related_products();
    
    // Remove the filter to avoid affecting other parts of the site
    remove_all_filters('woocommerce_related_products', 100);
}

/**
 * Remove the default related products section
 */
function inksplosion_remove_default_related() {
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}
add_action('init', 'inksplosion_remove_default_related');
