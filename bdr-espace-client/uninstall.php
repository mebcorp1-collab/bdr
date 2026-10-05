<?php
/**
 * Uninstall: only settings are removed.
 *
 * Dossiers, messages, documents and client accounts are deliberately KEPT: they are client
 * records the bank may be required to retain. Delete them manually if your retention policy allows.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bdr_ec_options' );
delete_option( 'bdr_ec_db_version' );
