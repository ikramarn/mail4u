<?php
/**
 * Fired when the plugin is deleted (not just deactivated).
 * Removes all plugin options and drops the campaigns table.
 */

// WordPress sets this constant before calling uninstall.php.
// If it is not defined, someone is trying to access this file directly — abort.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Remove all plugin options
$options = [
    'mail4u_stripe_pub_key',
    'mail4u_stripe_sec_key',
    'mail4u_stripe_wh_secret',
    'mail4u_admin_email',
    'mail4u_price_starter',
    'mail4u_price_pro',
    'mail4u_price_enterprise',
];
foreach ( $options as $option ) {
    delete_option( $option );
}

// Drop the campaigns table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mail4u_campaigns" );
