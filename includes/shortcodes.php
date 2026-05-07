<?php
defined( 'ABSPATH' ) || exit;

/* ── Register shortcodes ───────────────────────────────────────────────── */

add_shortcode( 'mail4u_homepage',  'mail4u_sc_homepage' );
add_shortcode( 'mail4u_pricing',   'mail4u_sc_pricing' );
add_shortcode( 'mail4u_register',  'mail4u_sc_register' );
add_shortcode( 'mail4u_dashboard', 'mail4u_sc_dashboard' );
add_shortcode( 'mail4u_contact',   'mail4u_sc_contact' );

/* ── Render helper ─────────────────────────────────────────────────────── */

function mail4u_render( $template, $vars = [] ) {
    // phpcs:ignore WordPress.PHP.DontExtract -- safe: all keys are hard-coded by us
    extract( $vars, EXTR_SKIP );
    ob_start();
    include MAIL4U_PATH . 'templates/' . $template . '.php';
    return ob_get_clean();
}

/* ── [mail4u_homepage] ─────────────────────────────────────────────────── */

function mail4u_sc_homepage() {
    return mail4u_render( 'homepage' );
}

/* ── [mail4u_pricing] ──────────────────────────────────────────────────── */

function mail4u_sc_pricing() {
    $notice = '';

    if ( isset( $_POST['mail4u_buy_plan'] ) ) {
        if ( ! wp_verify_nonce( $_POST['_m4u_plan_nonce'] ?? '', 'mail4u_buy_plan' ) ) {
            $notice = '<p class="m4u-notice m4u-error">Security check failed. Please try again.</p>';
        } elseif ( ! is_user_logged_in() ) {
            $notice = '<p class="m4u-notice m4u-error">Please <a href="' . esc_url( home_url( '/mail4u-register' ) ) . '">log in or register</a> before purchasing.</p>';
        } else {
            $plan    = sanitize_key( $_POST['mail4u_plan'] ?? '' );
            $user_id = get_current_user_id();
            $url     = mail4u_create_checkout_session( $plan, $user_id );

            if ( is_wp_error( $url ) ) {
                $notice = '<p class="m4u-notice m4u-error">' . esc_html( $url->get_error_message() ) . '</p>';
            } else {
                wp_redirect( $url );
                exit;
            }
        }
    }

    return mail4u_render( 'pricing', [ 'notice' => $notice ] );
}

/* ── [mail4u_register] ─────────────────────────────────────────────────── */

function mail4u_sc_register() {
    if ( is_user_logged_in() ) {
        return '<p class="m4u-notice">You are already logged in. <a href="' . esc_url( home_url( '/mail4u-dashboard' ) ) . '">Go to your dashboard &rarr;</a></p>';
    }

    $notice = '';

    // --- Handle login ---
    if ( isset( $_POST['mail4u_login'] ) ) {
        if ( ! wp_verify_nonce( $_POST['_m4u_log_nonce'] ?? '', 'mail4u_login' ) ) {
            $notice = '<p class="m4u-notice m4u-error">Security check failed.</p>';
        } else {
            $user = wp_signon(
                [
                    'user_login'    => sanitize_user( $_POST['login_username'] ?? '' ),
                    'user_password' => $_POST['login_password'] ?? '',
                    'remember'      => true,
                ],
                is_ssl()
            );
            if ( is_wp_error( $user ) ) {
                $notice = '<p class="m4u-notice m4u-error">' . esc_html( $user->get_error_message() ) . '</p>';
            } else {
                wp_redirect( home_url( '/mail4u-dashboard' ) );
                exit;
            }
        }
    }

    // --- Handle registration ---
    if ( isset( $_POST['mail4u_register'] ) ) {
        if ( ! wp_verify_nonce( $_POST['_m4u_reg_nonce'] ?? '', 'mail4u_register' ) ) {
            $notice = '<p class="m4u-notice m4u-error">Security check failed.</p>';
        } else {
            $username = sanitize_user( $_POST['reg_username'] ?? '' );
            $email    = sanitize_email( $_POST['reg_email'] ?? '' );
            $password = $_POST['reg_password'] ?? '';
            $confirm  = $_POST['reg_confirm'] ?? '';

            if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
                $notice = '<p class="m4u-notice m4u-error">All fields are required.</p>';
            } elseif ( ! is_email( $email ) ) {
                $notice = '<p class="m4u-notice m4u-error">Please enter a valid email address.</p>';
            } elseif ( $password !== $confirm ) {
                $notice = '<p class="m4u-notice m4u-error">Passwords do not match.</p>';
            } elseif ( strlen( $password ) < 8 ) {
                $notice = '<p class="m4u-notice m4u-error">Password must be at least 8 characters.</p>';
            } else {
                $user_id = wp_create_user( $username, $password, $email );
                if ( is_wp_error( $user_id ) ) {
                    $notice = '<p class="m4u-notice m4u-error">' . esc_html( $user_id->get_error_message() ) . '</p>';
                } else {
                    update_user_meta( $user_id, 'mail4u_plan', 'free' );
                    mail4u_send_welcome( $user_id );
                    // Auto sign-in and send to pricing
                    wp_signon( [ 'user_login' => $username, 'user_password' => $password, 'remember' => true ], is_ssl() );
                    wp_redirect( home_url( '/mail4u-pricing' ) );
                    exit;
                }
            }
        }
    }

    return mail4u_render( 'register', [ 'notice' => $notice ] );
}

/* ── [mail4u_dashboard] ────────────────────────────────────────────────── */

function mail4u_sc_dashboard() {
    if ( ! is_user_logged_in() ) {
        return '<p class="m4u-notice">Please <a href="' . esc_url( home_url( '/mail4u-register' ) ) . '">log in</a> to access your dashboard.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $notice  = '';

    // --- Handle campaign submission ---
    if ( isset( $_POST['mail4u_campaign'] ) ) {
        if ( ! wp_verify_nonce( $_POST['_m4u_camp_nonce'] ?? '', 'mail4u_campaign' ) ) {
            $notice = '<p class="m4u-notice m4u-error">Security check failed.</p>';
        } else {
            $industry  = sanitize_text_field( $_POST['industry'] ?? '' );
            $deal_type = sanitize_text_field( $_POST['deal_type'] ?? '' );
            $message   = sanitize_textarea_field( $_POST['message'] ?? '' );

            if ( empty( $industry ) || empty( $deal_type ) || empty( $message ) ) {
                $notice = '<p class="m4u-notice m4u-error">All fields are required.</p>';
            } else {
                $table    = $wpdb->prefix . 'mail4u_campaigns';
                $inserted = $wpdb->insert(
                    $table,
                    [
                        'user_id'   => $user_id,
                        'industry'  => $industry,
                        'deal_type' => $deal_type,
                        'message'   => $message,
                        'status'    => 'pending',
                    ],
                    [ '%d', '%s', '%s', '%s', '%s' ]
                );

                if ( $inserted ) {
                    mail4u_send_campaign_confirmation( $user_id, $wpdb->insert_id );
                    $notice = '<p class="m4u-notice m4u-success">Campaign submitted! We will start your outreach shortly.</p>';
                } else {
                    $notice = '<p class="m4u-notice m4u-error">Could not save your campaign. Please try again.</p>';
                }
            }
        }
    }

    $table     = $wpdb->prefix . 'mail4u_campaigns';
    $campaigns = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC", $user_id )
    );
    $plan = get_user_meta( $user_id, 'mail4u_plan', true ) ?: 'free';

    return mail4u_render( 'dashboard', [
        'notice'    => $notice,
        'campaigns' => $campaigns,
        'plan'      => $plan,
        'user'      => get_userdata( $user_id ),
    ] );
}

/* ── [mail4u_contact] ──────────────────────────────────────────────────── */

function mail4u_sc_contact() {
    $notice = '';

    if ( isset( $_POST['mail4u_contact'] ) ) {
        if ( ! wp_verify_nonce( $_POST['_m4u_ctct_nonce'] ?? '', 'mail4u_contact' ) ) {
            $notice = '<p class="m4u-notice m4u-error">Security check failed.</p>';
        } else {
            $name    = sanitize_text_field( $_POST['contact_name'] ?? '' );
            $email   = sanitize_email( $_POST['contact_email'] ?? '' );
            $subject = sanitize_text_field( $_POST['contact_subject'] ?? '' );
            $message = sanitize_textarea_field( $_POST['contact_message'] ?? '' );

            if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
                $notice = '<p class="m4u-notice m4u-error">Name, email, and message are required.</p>';
            } elseif ( ! is_email( $email ) ) {
                $notice = '<p class="m4u-notice m4u-error">Please enter a valid email address.</p>';
            } else {
                mail4u_send_contact_notification( $name, $email, $subject, $message );
                $notice = '<p class="m4u-notice m4u-success">Message sent! We will reply within 24 hours.</p>';
            }
        }
    }

    return mail4u_render( 'contact', [ 'notice' => $notice ] );
}
