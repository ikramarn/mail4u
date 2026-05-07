<?php
defined( 'ABSPATH' ) || exit;

/* ── Webhook endpoint (registered before headers are sent) ─────────────── */

add_action( 'init', function () {
    if ( isset( $_GET['mail4u_webhook'] ) && $_GET['mail4u_webhook'] === '1' ) {
        mail4u_process_webhook();
        exit;
    }
} );

/* ── Success redirect handler ──────────────────────────────────────────── */

add_action( 'template_redirect', 'mail4u_handle_stripe_success' );

/**
 * After Stripe redirects back with ?mail4u_action=stripe_success, verify
 * the session against the Stripe API and update the user's plan.
 */
function mail4u_handle_stripe_success() {
    if (
        ! isset( $_GET['mail4u_action'] )
        || $_GET['mail4u_action'] !== 'stripe_success'
    ) {
        return;
    }

    $plan       = sanitize_key( $_GET['plan'] ?? '' );
    $user_id    = absint( $_GET['uid'] ?? 0 );
    $session_id = sanitize_text_field( $_GET['session_id'] ?? '' );
    $secret_key = get_option( 'mail4u_stripe_sec_key', '' );

    if ( ! $plan || ! $user_id || ! $session_id || ! $secret_key ) {
        return;
    }

    // Verify with Stripe that the session was actually paid
    $response = wp_remote_get(
        'https://api.stripe.com/v1/checkout/sessions/' . urlencode( $session_id ),
        [
            'headers' => [ 'Authorization' => 'Bearer ' . $secret_key ],
            'timeout' => 15,
        ]
    );

    if ( is_wp_error( $response ) ) {
        return;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( isset( $body['payment_status'] ) && $body['payment_status'] === 'paid' ) {
        // Read plan and user ID from the verified Stripe session rather than
        // from URL parameters, which the user could tamper with.
        $ref           = $body['client_reference_id'] ?? '';
        $parts         = explode( '|', $ref );
        $verified_uid  = absint( $parts[0] ?? 0 );
        $verified_plan = sanitize_key( $parts[1] ?? '' );

        $allowed_plans = [ 'starter', 'pro', 'enterprise' ];
        if ( $verified_uid && in_array( $verified_plan, $allowed_plans, true ) ) {
            update_user_meta( $verified_uid, 'mail4u_plan', $verified_plan );
            update_user_meta( $verified_uid, 'mail4u_plan_activated', current_time( 'mysql' ) );
        }
    }
}

/* ── Create Checkout Session ───────────────────────────────────────────── */

/**
 * Create a Stripe Checkout Session and return the hosted URL.
 *
 * @param  string   $plan    'starter' | 'pro' | 'enterprise'
 * @param  int      $user_id WP user ID
 * @return string|WP_Error   Checkout URL or WP_Error
 */
function mail4u_create_checkout_session( $plan, $user_id ) {
    $secret_key = get_option( 'mail4u_stripe_sec_key', '' );
    if ( empty( $secret_key ) ) {
        return new WP_Error( 'no_key', __( 'Payment is not configured yet. Please contact support.', 'mail4u' ) );
    }

    $amounts = [
        'starter'    => (int) get_option( 'mail4u_price_starter',    '2900' ),
        'pro'        => (int) get_option( 'mail4u_price_pro',        '7900' ),
        'enterprise' => (int) get_option( 'mail4u_price_enterprise', '19900' ),
    ];
    $labels = [
        'starter'    => 'Mail4U Starter Plan (Monthly)',
        'pro'        => 'Mail4U Pro Plan (Monthly)',
        'enterprise' => 'Mail4U Enterprise Plan (Monthly)',
    ];

    if ( ! array_key_exists( $plan, $amounts ) ) {
        return new WP_Error( 'invalid_plan', __( 'Invalid plan selected.', 'mail4u' ) );
    }

    // NOTE: {CHECKOUT_SESSION_ID} must NOT be passed through add_query_arg because
    // that function URL-encodes the braces (%7B…%7D) and Stripe would no longer
    // recognise the template token and substitute it with the real session ID.
    $success_url = add_query_arg(
        [
            'mail4u_action' => 'stripe_success',
            'plan'          => $plan,
            'uid'           => $user_id,
        ],
        home_url( '/mail4u-dashboard' )
    ) . '&session_id={CHECKOUT_SESSION_ID}';

    $response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $secret_key,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ],
        'body' => [
            'payment_method_types[0]'                             => 'card',
            'line_items[0][price_data][currency]'                 => 'usd',
            'line_items[0][price_data][unit_amount]'              => $amounts[ $plan ],
            'line_items[0][price_data][product_data][name]'       => $labels[ $plan ],
            'line_items[0][quantity]'                             => 1,
            'mode'                                                => 'payment',
            'success_url'                                         => $success_url,
            'cancel_url'                                          => home_url( '/mail4u-pricing' ),
            'client_reference_id'                                 => $user_id . '|' . $plan,
            'customer_email'                                      => get_userdata( $user_id )->user_email,
        ],
        'timeout' => 15,
    ] );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( isset( $body['error'] ) ) {
        return new WP_Error( 'stripe_error', $body['error']['message'] );
    }

    return $body['url'] ?? new WP_Error( 'no_url', __( 'Could not create checkout session.', 'mail4u' ) );
}

/* ── Webhook processor ─────────────────────────────────────────────────── */

function mail4u_process_webhook() {
    $wh_secret = get_option( 'mail4u_stripe_wh_secret', '' );
    $payload   = file_get_contents( 'php://input' );
    $sig       = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] )
                    ? sanitize_text_field( $_SERVER['HTTP_STRIPE_SIGNATURE'] )
                    : '';

    if ( ! empty( $wh_secret ) && ! mail4u_verify_stripe_signature( $payload, $sig, $wh_secret ) ) {
        status_header( 400 );
        echo 'Invalid signature';
        exit;
    }

    $event = json_decode( $payload, true );
    if ( ! isset( $event['type'] ) ) {
        status_header( 400 );
        exit;
    }

    if ( $event['type'] === 'checkout.session.completed' ) {
        $ref     = $event['data']['object']['client_reference_id'] ?? '';
        $parts   = explode( '|', $ref );
        $user_id = absint( $parts[0] ?? 0 );
        $plan    = sanitize_key( $parts[1] ?? '' );

        if ( $user_id && $plan ) {
            update_user_meta( $user_id, 'mail4u_plan', $plan );
            update_user_meta( $user_id, 'mail4u_plan_activated', current_time( 'mysql' ) );
        }
    }

    status_header( 200 );
    echo 'OK';
    exit;
}

/* ── Stripe signature verification ────────────────────────────────────── */

/**
 * Verify a Stripe webhook signature header without using the Stripe SDK.
 *
 * @param  string $payload    Raw request body
 * @param  string $sig_header Value of the Stripe-Signature header
 * @param  string $secret     Webhook signing secret from Stripe dashboard
 * @return bool
 */
function mail4u_verify_stripe_signature( $payload, $sig_header, $secret ) {
    $timestamp  = '';
    $signatures = [];

    foreach ( explode( ',', $sig_header ) as $part ) {
        if ( strpos( $part, 't=' ) === 0 ) {
            $timestamp = substr( $part, 2 );
        } elseif ( strpos( $part, 'v1=' ) === 0 ) {
            $signatures[] = substr( $part, 3 );
        }
    }

    if ( ! $timestamp || empty( $signatures ) ) {
        return false;
    }

    $expected = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );

    foreach ( $signatures as $sig ) {
        if ( hash_equals( $expected, $sig ) ) {
            return true;
        }
    }

    return false;
}
