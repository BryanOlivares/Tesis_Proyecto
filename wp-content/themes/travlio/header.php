<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Travlio
 * @since Travlio 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="//gmpg.org/xfn/11">

	<?php wp_head(); ?>
	<style type="text/css">
.modal-backdrop.fade {
    opacity: 1;
    filter: alpha(opacity=0);
    display: none !important;
}

.modal-open .modal {
    display: none !important;
}




.fade {
    opacity: 1 !important;
}

.xoo-el-form-container button.btn.button.xoo-el-action-btn {
    height: 57px !important;
}

.tour-grid .tour-favorite {
    display: none;
}

.tour-detail-map {
    display: none;
}

div#apus-header {
    background: #fff !important;
}

body.single-to_book {
    background-color: #fff !important;
}

.visible-xs, .visible-sm, .visible-md, .visible-lg {
    display: block !important;
}

.admin-bar .header-mobile {
    display: none;
}
li#wp-admin-bar-_options {
     display: none !important;
}

li#wp-admin-bar-new-content {
     display: none !important;
}

span.ab-label.awaiting-mod.pending-count.count-0 {
    display: none !important;
}

li#wp-admin-bar-comments {
    display: none;
}
li#wp-admin-bar-elementor_edit_page {
    display: none;
}
li#wp-admin-bar-edit {
    display: none;
}
li#wp-admin-bar-customize {
    display: none;
}
@media only screen and (max-width: 990px){

.elementor-element.elementor-element-1e2d282.elementor-widget__width-auto.elementor-widget.elementor-widget-apus_user_info {
    margin-top: -50px !important;
}
.admin-bar .header-mobile {
    display: none;
}

.profile-menus.flex-middle.clearfix {
    margin-right: 20px;
    margin-bottom: 10px;
}


.elementor-106 .elementor-element.elementor-element-4dcff85 img {
    max-width: 100px;
}
}
</style>

	 	 <style type="text/css">
.account-login {
    display: none;
}

.ocultame {
    display: none;
}
.detail-tour #comments {
    display: none !important;
}


#wpadminbar .ab-submenu {
    display: none;
}
</style>







</head>
<body <?php body_class(); ?>>
    <?php echo do_shortcode('[exclusivo]

 	 <style type="text/css">
.account-login {
    display: block !important;
}
.ocultame {
    display: block !important;
}



</style>


[/exclusivo]'); ?>
<?php if ( travlio_get_config('preload', true) ) {
	$preload_icon = travlio_get_config('media-preload-icon');
	$preload_icon_image_img = '';
	if ( (isset($preload_icon['url'])) && (trim($preload_icon['url']) != "" ) ) {
        if (is_ssl()) {
            $preload_icon_image_img = str_replace("http://", "https://", $preload_icon['url']);		
        } else {
            $preload_icon_image_img = $preload_icon['url'];
        }
    }
?>
	<div class="apus-page-loading">
        <div class="apus-loader-inner" style="<?php echo esc_attr($preload_icon_image_img ? 'background-image: url(\''.$preload_icon_image_img.'\')' : ''); ?>"></div>
    </div>
<?php } ?>
<div id="wrapper-container" class="wrapper-container">
	
	<?php
    if ( function_exists( 'wp_body_open' ) ) {
        wp_body_open();
    }
    ?>
    
	<?php get_template_part( 'headers/mobile/offcanvas-menu' ); ?>
	<?php get_template_part( 'headers/mobile/header-mobile' ); ?>

	<?php
		$header = apply_filters( 'travlio_get_header_layout', travlio_get_config('header_type') );
		if ( !empty($header) ) {
			travlio_display_header_builder($header);
		} else {
			get_template_part( 'headers/default' );
		}
	?>
	<div id="apus-main-content">