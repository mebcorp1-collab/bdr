<?php
/**
 * Uninstall: only settings are removed.
 *
 * FAQ entries, subscription requests, client dossiers, messages, documents and client accounts
 * are deliberately KEPT: they are client records the bank may be required to retain.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bdr_eb_options' );
delete_option( 'bdr_ec_options' );
delete_option( 'bdr_ec_db_version' );
delete_option( 'bdr_ec_page_checked' );
