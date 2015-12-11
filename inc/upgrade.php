<?php

add_action( 'wppaytpv_upgrade_version', 'payptv_upgrade', 10, 2 );

function payptv_upgrade( $new_ver, $old_ver ) {
	global $wpdb;

	if ( ! version_compare( $old_ver, '3.0', '<' ) )
		return;

	$table_name = $wpdb->prefix . "paytpv_customer";
	$charset_collate = $wpdb->get_charset_collate();

	// Si no existe la tabla la creamos
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" )==false ) {
		$sql = "CREATE TABLE $table_name (
		`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`paytpv_iduser` INT(11) UNSIGNED NOT NULL,
		`paytpv_tokenuser` VARCHAR(64) NOT NULL,
		`paytpv_cc` VARCHAR(32) NOT NULL,
		`paytpv_brand` VARCHAR(32) NULL DEFAULT NULL,
		`id_customer` INT(10) UNSIGNED NOT NULL,
		`date` DATETIME NOT NULL,
		`card_desc` VARCHAR(32) NULL DEFAULT NULL,
		PRIMARY KEY (`id`)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	} 
}

?>