<?php
defined( 'ABSPATH' ) || exit;

/**
 * Send a welcome email to a newly registered user.
 *
 * @param int $user_id
 */
function mail4u_send_welcome( $user_id ) {
    $user    = get_userdata( $user_id );
    $to      = $user->user_email;
    $subject = 'Welcome to Mail4U';
    $body    = sprintf(
        "Hi %s,\n\nWelcome to Mail4U! Your account is ready.\n\nHead to your dashboard to launch your first direct mail campaign:\n%s\n\n\u2014 The Mail4U Team",
        $user->display_name,
        home_url( '/mail4u-dashboard' )
    );
    wp_mail( $to, $subject, $body, [ 'Content-Type: text/plain; charset=UTF-8' ] );
}

/**
 * Notify the admin about a new contact form submission.
 *
 * @param string $name
 * @param string $email
 * @param string $user_subject
 * @param string $message
 */
function mail4u_send_contact_notification( $name, $email, $user_subject, $message ) {
    $admin          = get_option( 'mail4u_admin_email', get_option( 'admin_email' ) );
    $subject_suffix = $user_subject ? ' — ' . sanitize_text_field( $user_subject ) : '';
    $subject        = 'New Contact Form Submission' . $subject_suffix . ' — Mail4U';
    $body           = sprintf(
        "Name:    %s\nEmail:   %s\nSubject: %s\n\nMessage:\n%s",
        sanitize_text_field( $name ),
        sanitize_email( $email ),
        sanitize_text_field( $user_subject ),
        sanitize_textarea_field( $message )
    );
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . sanitize_email( $email ),
    ];
    wp_mail( $admin, $subject, $body, $headers );
}

/**
 * Confirm a newly submitted campaign to the user.
 *
 * @param int $user_id
 * @param int $campaign_id
 */
function mail4u_send_campaign_confirmation( $user_id, $campaign_id ) {
    $user    = get_userdata( $user_id );
    $to      = $user->user_email;
    $subject = 'Campaign #' . absint( $campaign_id ) . ' Received — Mail4U';
    $body    = sprintf(
        "Hi %s,\n\nYour mailing campaign (#%d) has been received and is now in the print queue.\n\nWe will notify you once your mail pieces have been dispatched.\n\n\u2014 The Mail4U Team",
        $user->display_name,
        absint( $campaign_id )
    );
    wp_mail( $to, $subject, $body, [ 'Content-Type: text/plain; charset=UTF-8' ] );
}
