<?php
/**
 * Plugin Name:       Mail4U
 * Plugin URI:        https://mail4u.example.com
 * Description:       B2B Mail Outreach SaaS — sends cold outreach emails to newly launched businesses on behalf of your customers.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Mail4U
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mail4u
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'MAIL4U_VERSION', '1.0.0' );
define( 'MAIL4U_PATH',    plugin_dir_path( __FILE__ ) );
define( 'MAIL4U_URL',     plugin_dir_url( __FILE__ ) );

require_once MAIL4U_PATH . 'includes/activation.php';
require_once MAIL4U_PATH . 'includes/mail.php';
require_once MAIL4U_PATH . 'includes/stripe.php';
require_once MAIL4U_PATH . 'includes/admin-settings.php';
require_once MAIL4U_PATH . 'includes/shortcodes.php';

register_activation_hook( __FILE__, 'mail4u_activate' );
register_deactivation_hook( __FILE__, 'mail4u_deactivate' );

add_action( 'plugins_loaded', function () {
    load_plugin_textdomain( 'mail4u', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

add_action( 'wp_enqueue_scripts', 'mail4u_enqueue_assets' );

function mail4u_enqueue_assets() {
    wp_enqueue_style(
        'mail4u-style',
        MAIL4U_URL . 'assets/style.css',
        [],
        MAIL4U_VERSION
    );
    wp_enqueue_script(
        'mail4u-script',
        MAIL4U_URL . 'assets/script.js',
        [ 'jquery' ],
        MAIL4U_VERSION,
        true
    );
}
