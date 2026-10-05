<?php
/**
 * Remove plugin settings on uninstall. Saved contact messages are kept on purpose.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bdr_toolkit_options' );
