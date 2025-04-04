<?php
/**
 * Profile Photo endpoint template
 *
 * This template is used to display the profile photo management interface
 * in the My Account section.
 *
 * @package Minimog Child
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$user_id = get_current_user_id();
$avatar_id = get_user_meta($user_id, 'custom_avatar', true);
$current_avatar_url = $avatar_id ? wp_get_attachment_url($avatar_id) : get_avatar_url($user_id);
?>

<div class="woocommerce-profile-photo">
    <h2><?php esc_html_e('Profile Photo', 'minimog-child'); ?></h2>
    
    <div class="profile-photo-content">
        <div class="current-profile-photo">
            <div class="photo-preview">
                <?php if ($avatar_id) : ?>
                    <img src="<?php echo esc_url($current_avatar_url); ?>" alt="<?php esc_attr_e('Current profile photo', 'minimog-child'); ?>" />
                <?php else : ?>
                    <?php echo get_avatar($user_id, 200); ?>
                <?php endif; ?>
            </div>
            <p class="description"><?php esc_html_e('Your current profile photo', 'minimog-child'); ?></p>
        </div>
        
        <div class="profile-photo-management">
            <div class="upload-new-photo">
                <h3><?php esc_html_e('Upload New Photo', 'minimog-child'); ?></h3>
                
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('update_profile_photo', 'profile_photo_nonce'); ?>
                    
                    <div class="form-row">
                        <label for="profile_photo"><?php esc_html_e('Select an image', 'minimog-child'); ?></label>
                        <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/png,image/gif" required />
                        <p class="description">
                            <?php esc_html_e('JPG, PNG or GIF. Maximum file size 1MB.', 'minimog-child'); ?>
                        </p>
                    </div>
                    
                    <div class="form-row">
                        <button type="submit" name="update_profile_photo" class="button"><?php esc_html_e('Upload', 'minimog-child'); ?></button>
                    </div>
                </form>
            </div>
            
            <?php if ($avatar_id) : ?>
                <div class="remove-photo">
                    <h3><?php esc_html_e('Remove Photo', 'minimog-child'); ?></h3>
                    <p><?php esc_html_e('This will remove your custom profile photo and reset to the default avatar.', 'minimog-child'); ?></p>
                    
                    <form method="post">
                        <?php wp_nonce_field('update_profile_photo', 'profile_photo_nonce'); ?>
                        <button type="submit" name="remove_profile_photo" class="button"><?php esc_html_e('Remove Photo', 'minimog-child'); ?></button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
