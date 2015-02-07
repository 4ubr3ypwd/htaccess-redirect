<?php

/*
Plugin Name: .htaccess Redirect
Plugin URI: https://github.com/aubreypwd/htaccess-redirect
Description: This plugin modifies your .htaccess file to redirect requests to new locations. This is especially useful (and intended) to redirect requests to web locations/pages outside of your WordPress installation to pages now in WordPress.
Author: Aubrey Portwood
Version: 1.0-dev
Author URI: http://aubreypwd.com
*/

if ( ! function_exists( 'wp_htaccess_redirect') ) :
function wp_htaccess_redirect() {
	if ( ! isset( $wp_Htaccess_Redirect ) ) {
		$wp_Htaccess_Redirect = new Htaccess_Redirect();
	}
}
endif;

add_action( 'init', 'wp_htaccess_redirect' );
