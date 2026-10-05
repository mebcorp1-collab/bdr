<?php
/**
 * Remove plugin settings on uninstall. FAQ entries and subscription requests are kept on purpose.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bdr_eb_options' );
