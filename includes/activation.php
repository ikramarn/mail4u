<?php
defined( 'ABSPATH' ) || exit;

/**
 * Runs on plugin activation.
 * Creates the campaigns table, default options, and starter pages.
 */
function mail4u_activate() {
    global $wpdb;

    $table           = $wpdb->prefix . 'mail4u_campaigns';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id         bigint(20)   NOT NULL AUTO_INCREMENT,
        user_id    bigint(20)   NOT NULL,
        industry   varchar(255) NOT NULL,
        deal_type  varchar(100) NOT NULL,
        message    text         NOT NULL,
        status     varchar(50)  NOT NULL DEFAULT 'pending',
        created_at datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    // Default plugin options (only set if they don't exist yet)
    add_option( 'mail4u_stripe_pub_key',   '' );
    add_option( 'mail4u_stripe_sec_key',   '' );
    add_option( 'mail4u_stripe_wh_secret', '' );
    add_option( 'mail4u_admin_email',      get_option( 'admin_email' ) );
    add_option( 'mail4u_price_starter',    '2900' );  // in cents
    add_option( 'mail4u_price_pro',        '7900' );
    add_option( 'mail4u_price_enterprise', '19900' );

    mail4u_create_pages();
    flush_rewrite_rules();
}

/**
 * Create the required WP pages and assign shortcodes, skipping any that already exist.
 */
function mail4u_create_pages() {
    $pages = [
        'mail4u-home'      => [ 'title' => 'Mail4U Home',   'shortcode' => '[mail4u_homepage]' ],
        'mail4u-pricing'   => [ 'title' => 'Pricing',        'shortcode' => '[mail4u_pricing]' ],
        'mail4u-register'  => [ 'title' => 'Get Started',    'shortcode' => '[mail4u_register]' ],
        'mail4u-dashboard' => [ 'title' => 'My Dashboard',   'shortcode' => '[mail4u_dashboard]' ],
        'mail4u-contact'   => [ 'title' => 'Contact Us',     'shortcode' => '[mail4u_contact]' ],
    ];

    foreach ( $pages as $slug => $data ) {
        if ( get_page_by_path( $slug ) ) {
            continue;
        }
        wp_insert_post( [
            'post_title'   => $data['title'],
            'post_name'    => $slug,
            'post_content' => $data['shortcode'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
    }
}

/**
 * Runs on plugin deactivation.
 */
function mail4u_deactivate() {
    flush_rewrite_rules();
}
