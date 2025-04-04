<?php
/**
 * WooCommerce My Account customization functions
 *
 * This file contains the functions required to set up the custom My Account
 * functionality for the Minimog child theme.
 *
 * @package Minimog Child
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register custom endpoint for profile photo
 */
function minimog_child_add_profile_photo_endpoint() {
    add_rewrite_endpoint('profile-photo', EP_ROOT | EP_PAGES);
}
add_action('init', 'minimog_child_add_profile_photo_endpoint');

/**
 * Add new query vars
 *
 * @param array $vars
 * @return array
 */
function minimog_child_profile_photo_query_vars($vars) {
    $vars[] = 'profile-photo';
    return $vars;
}
add_filter('query_vars', 'minimog_child_profile_photo_query_vars');

/**
 * Add Profile Photo to My Account menu items
 *
 * @param array $items
 * @return array
 */
function minimog_child_add_profile_photo_link_my_account($items) {
    // Add profile photo item after account details
    $account_details_position = array_search('edit-account', array_keys($items));
    
    if ($account_details_position !== false) {
        $account_details_position += 1;
        
        $items = array_slice($items, 0, $account_details_position, true) +
            array('profile-photo' => __('Profile Photo', 'minimog-child')) +
            array_slice($items, $account_details_position, count($items) - $account_details_position, true);
    } else {
        // If edit-account is not found, just append to the end before logout
        $logout_position = array_search('customer-logout', array_keys($items));
        if ($logout_position !== false) {
            $logout_position += 0;
            
            $items = array_slice($items, 0, $logout_position, true) +
                array('profile-photo' => __('Profile Photo', 'minimog-child')) +
                array_slice($items, $logout_position, count($items) - $logout_position, true);
        } else {
            // If all else fails, just add it to the end
            $items['profile-photo'] = __('Profile Photo', 'minimog-child');
        }
    }
    
    return $items;
}
add_filter('woocommerce_account_menu_items', 'minimog_child_add_profile_photo_link_my_account');

/**
 * Profile Photo endpoint content
 */
function minimog_child_profile_photo_endpoint_content() {
    // Get current user
    $user_id = get_current_user_id();
    
    // Handle form submission
    if (isset($_POST['update_profile_photo']) && isset($_FILES['profile_photo'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Check nonce
        if (!isset($_POST['profile_photo_nonce']) || 
            !wp_verify_nonce($_POST['profile_photo_nonce'], 'update_profile_photo')) {
            wc_add_notice(__('Security check failed. Please try again.', 'minimog-child'), 'error');
        } else {
            // Handle file upload
            $attachment_id = media_handle_upload('profile_photo', 0);
            
            if (is_wp_error($attachment_id)) {
                wc_add_notice($attachment_id->get_error_message(), 'error');
            } else {
                // Get previous avatar to delete it
                $previous_avatar_id = get_user_meta($user_id, 'custom_avatar', true);
                
                // Update user meta with new avatar
                update_user_meta($user_id, 'custom_avatar', $attachment_id);
                
                // Delete previous avatar if exists
                if ($previous_avatar_id) {
                    wp_delete_attachment($previous_avatar_id, true);
                }
                
                wc_add_notice(__('Profile photo updated successfully!', 'minimog-child'), 'success');
            }
        }
    }
    
    // Handle removing profile photo
    if (isset($_POST['remove_profile_photo'])) {
        // Check nonce
        if (!isset($_POST['profile_photo_nonce']) || 
            !wp_verify_nonce($_POST['profile_photo_nonce'], 'update_profile_photo')) {
            wc_add_notice(__('Security check failed. Please try again.', 'minimog-child'), 'error');
        } else {
            // Get avatar ID
            $avatar_id = get_user_meta($user_id, 'custom_avatar', true);
            
            if ($avatar_id) {
                // Delete the attachment
                wp_delete_attachment($avatar_id, true);
                
                // Remove user meta
                delete_user_meta($user_id, 'custom_avatar');
                
                wc_add_notice(__('Profile photo removed successfully!', 'minimog-child'), 'success');
            }
        }
    }
    
    // Add title for the Profile Photo page
    add_filter('woocommerce_endpoint_profile-photo_title', function() {
        return __('Profile Photo', 'minimog-child');
    });
    
    // Display the form
    wc_get_template(
        'myaccount/profile-photo.php',
        array(),
        'minimog-child/',
        get_stylesheet_directory() . '/woocommerce/myaccount/'
    );
}
add_action('woocommerce_account_profile-photo_endpoint', 'minimog_child_profile_photo_endpoint_content');

/**
 * Add the Dashboard page title
 */
add_filter('woocommerce_endpoint_dashboard_title', function() {
    return __('My Account', 'minimog-child');
});

/**
 * Register and enqueue custom styles and scripts for My Account page
 */
function minimog_child_myaccount_scripts() {
    if (is_account_page()) {
        wp_enqueue_style(
            'minimog-child-myaccount',
            get_stylesheet_directory_uri() . '/assets/css/myaccount.css',
            array(),
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'minimog_child_myaccount_scripts');

/**
 * Filter to use custom avatar in comments and other places
 */
function minimog_child_get_custom_avatar($avatar, $id_or_email, $size, $default, $alt) {
    $user = false;
    
    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
        $user = get_user_by('id', $user_id);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $user = get_user_by('id', (int) $id_or_email->user_id);
        }
    } else {
        $user = get_user_by('email', $id_or_email);
    }
    
    if ($user && is_object($user)) {
        $custom_avatar_id = get_user_meta($user->ID, 'custom_avatar', true);
        
        if ($custom_avatar_id) {
            $image = wp_get_attachment_image_src($custom_avatar_id, array($size, $size));
            
            if ($image) {
                $avatar = "<img alt='{$alt}' src='{$image[0]}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            }
        }
    }
    
    return $avatar;
}
add_filter('get_avatar', 'minimog_child_get_custom_avatar', 10, 5);

/**
 * Override default WooCommerce templates for My Account pages
 * 
 * @param string $template
 * @param string $template_name
 * @param array $args
 * @param string $template_path
 * @param string $default_path
 * @return string
 */
function minimog_child_woocommerce_locate_template($template, $template_name, $args, $template_path, $default_path) {
    // Only modify myaccount templates
    if (strpos($template_name, 'myaccount/') === 0) {
        // Check if the template exists in our child theme
        $child_theme_template = get_stylesheet_directory() . '/woocommerce/' . $template_name;
        
        if (file_exists($child_theme_template)) {
            $template = $child_theme_template;
        }
    }
    
    return $template;
}
add_filter('wc_get_template', 'minimog_child_woocommerce_locate_template', 10, 5);
