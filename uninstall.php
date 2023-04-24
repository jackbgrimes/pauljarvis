<?php
#Uninstall script for CPJ Calendar Schedule Plugin

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}



// drop a custom database table
global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS wp_cpj_appt_email_content" );
$wpdb->query( "DROP TABLE IF EXISTS wp_cpj_appt_schedule" );
$wpdb->query( "DROP TABLE IF EXISTS wp_cpj_appt_users" );


