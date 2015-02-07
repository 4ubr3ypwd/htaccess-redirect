<?php

if ( ! class_exists( 'Htaccess_Redirect') ) :
class Htaccess_Redirect {
	private $olr_comment;
	private $olr;
	private $htaccess;

	function __construct() {
		$this->olr = get_option( 'olr' );
		$this->olr_comment = '#A redirect by .htaccess Redirect Plugin';
		$this->htaccess = $htaccess = get_option( 'olr_htaccess' ) . '.htaccess';

		add_action( 'init', array( $this, 'actions' ) );
		add_action( 'admin_menu', array( $this, 'olr_admin' ) );

		// Validation (TODO)
	}

	function request( $key ) {
		if ( isset( $_REQUEST[ $key ] ) ) {
			return apply_filters( "olr_$key", $_REQUEST[ $key ], __FUNCTION__ );
		}
	}

	function olr_admin() {
		add_submenu_page( 'tools.php', __( '.htaccess Redirect', 'olr' ), __( '.htaccess Redirect', 'olr' ), 'manage_options', 'olr', array( $this, 'olr_options' ) );
	}

	function olr_options() {
		include 'template/admin.php';
	}

	function actions() {

		// Save
		if ( $this->request( 'save' ) ) {
			$link = $this->request( 'link' );
			$redirect = $this->request( 'redirect' );

			// Send the "sent" values back to the admin page.
			$olr_link_redirect_query = "&link=$link&redirect=$redirect";

			// Do not continue if the link is not valid.
			if ( ! esc_url( $link ) ) {
				header( "location:tools.php?page=olr&noturl=1$olr_link_redirect_query" );
				exit();
			}

			// Strip scheme from URL.
			$parsed = parse_url( $link );
			$link = $parsed['path'];

			// ???
			if ( $link[0] != "/" ) {
				header( "location:tools.php?page=olr&noturl=1$olr_link_redirect_query" );
				exit();
			}
			if ( ! esc_url( $redirect ) ) {
				header( "location:tools.php?page=olr&noturl=1$olr_link_redirect_query" );
				exit();
			}

			// Error checking
			if ( ! isset( $parsed['path'] ) ) {
				header( "location:tools.php?page=olr&parsed=1$olr_link_redirect_query" );
				exit();
			}
			if ( $link == '' || $redirect == '' ) {
				header( "location:tools.php?page=olr&novalue=1$olr_link_redirect_query" );
				exit();
			}

			// Test if the re-direct already exists.
			$exists = false;
			if ( is_array( $olr ) ) {
				foreach ( $olr as $olr_item ) {
					if ( $link == $olr_item['link'] && $redirect == $olr_item['redirect'] ) {
						$exists=true;
					} else {
						$exists=false;
					}
				}
			}

			if ( ! $exists ) {
					//Add the RedirectMatch to the .htaccess file
					$old_htaccess = file_get_contents( $htaccess );
					$new_htaccess = $old_htaccess . "\n\n" . $olr_comment . "\nRedirectMatch 301 \"^" . fixUolr( urldecode( $link ) ) . '$" "' . fixUolr( urldecode( $redirect ) ) . '"';

					//write the changes
					$htacces_error = "0";
					$fh = fopen( $htaccess, 'w' ) or $htacces_error="1";
					fwrite( $fh, $new_htaccess );
					fclose( $fh );

					// Save the record in the DB
					if ( $htacces_error == "0" ) {
						$olr_add['link'] = $link;
						$olr_add['redirect'] = $redirect;
						$olr[] = $olr_add; //add it to olr array
						update_option( 'olr', $olr );
					}

				header( "location:tools.php?page=olr&saved=true&htaccess_error=$htacces_error" );
			} else {
				header( "location:tools.php?page=olr&exists=1$olr_link_redirect_query" );
			}
		}

		// ???
		if ( ( $this->request( 'htaccess' ) ) ) {
			update_option( 'olr_htaccess', $this->request( 'htaccess' ) );
			header( 'location:tools.php?page=olr&deleted=true' );
		}

		// Delete
		if ( ( $this->request( 'delete' ) ) ) {
			$id = $this->request( 'id' );

			//Get which link and redirect to search for (same as below)
			$j = 0;
			if ( is_array( $olr ) ) {
				foreach ($olr as $olr_item) {
					$j++;
						if ($j == $id) {
							$link=$olr_item['link'];
							$redirect=$olr_item['redirect'];
						}
				}
			}

			$htacces_error = NULL;
			if ( isset( $link ) && isset( $redirect ) ) {
				//Remove it from htaccess
				$old_htaccess = file_get_contents( $htaccess );
				$new_htaccess = str_replace( "\n\n" . $olr_comment . "\nRedirectMatch 301 \"^" . fixUolr( urldecode( $link ) ) . '$" "' . fixUolr( urldecode( $redirect ) ) . '"', '', $old_htaccess );

				//write the changes
				$htacces_error = "0";
				$fh = fopen( $htaccess, 'w' ) or $htacces_error = "1" ;
				fwrite( $fh, $new_htaccess );
				fclose( $fh );

				//Take it out of the DB
				$c = 0;
				if ( $htacces_error=="0" ) {
					foreach ( $olr as $olr_item ) {
						$c++;
						if ( $c != $id ) {
							$olr_new[] = $olr_item;
						} else {
							$olr_new = array();
						}
					}
					update_option( 'olr', $olr_new );
				}
			}

			header( "location:tools.php?page=olr&deleted=true&htaccess_error=$htacces_error" );
		}
	}

	function fixUolr( $d ) {
		$a = str_replace( "&", "\&", $d );
		$a = str_replace( "$", "\$", $a );
		$a = str_replace( "^", "\^", $a );
		return $a;
	}
}
endif;
