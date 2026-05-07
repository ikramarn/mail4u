<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'mail4u_add_admin_menu' );
add_action( 'admin_init', 'mail4u_register_settings' );

function mail4u_add_admin_menu() {
    add_menu_page(
        'Mail4U Settings',
        'Mail4U',
        'manage_options',
        'mail4u-settings',
        'mail4u_settings_page',
        'dashicons-email-alt',
        30
    );
    add_submenu_page(
        'mail4u-settings',
        'Campaigns',
        'Campaigns',
        'manage_options',
        'mail4u-campaigns',
        'mail4u_campaigns_page'
    );
}

function mail4u_register_settings() {
    $fields = [
        'mail4u_stripe_pub_key',
        'mail4u_stripe_sec_key',
        'mail4u_stripe_wh_secret',
        'mail4u_admin_email',
        'mail4u_price_starter',
        'mail4u_price_pro',
        'mail4u_price_enterprise',
    ];
    foreach ( $fields as $field ) {
        register_setting( 'mail4u_settings_group', $field, [ 'sanitize_callback' => 'sanitize_text_field' ] );
    }
}

function mail4u_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Mail4U — Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'mail4u_settings_group' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Stripe Publishable Key</th>
                    <td>
                        <input type="text"
                               name="mail4u_stripe_pub_key"
                               value="<?php echo esc_attr( get_option( 'mail4u_stripe_pub_key' ) ); ?>"
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Stripe Secret Key</th>
                    <td>
                        <input type="password"
                               name="mail4u_stripe_sec_key"
                               value="<?php echo esc_attr( get_option( 'mail4u_stripe_sec_key' ) ); ?>"
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Stripe Webhook Secret</th>
                    <td>
                        <input type="password"
                               name="mail4u_stripe_wh_secret"
                               value="<?php echo esc_attr( get_option( 'mail4u_stripe_wh_secret' ) ); ?>"
                               class="regular-text" />
                        <p class="description">
                            Register this URL in your Stripe dashboard:<br>
                            <code><?php echo esc_url( add_query_arg( 'mail4u_webhook', '1', home_url( '/' ) ) ); ?></code>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Admin Notification Email</th>
                    <td>
                        <input type="email"
                               name="mail4u_admin_email"
                               value="<?php echo esc_attr( get_option( 'mail4u_admin_email' ) ); ?>"
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Starter Plan Price (cents)</th>
                    <td>
                        <input type="number"
                               name="mail4u_price_starter"
                               value="<?php echo esc_attr( get_option( 'mail4u_price_starter', '2900' ) ); ?>"
                               class="small-text" min="0" />
                        <p class="description">e.g. 2900 = $29.00</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Pro Plan Price (cents)</th>
                    <td>
                        <input type="number"
                               name="mail4u_price_pro"
                               value="<?php echo esc_attr( get_option( 'mail4u_price_pro', '7900' ) ); ?>"
                               class="small-text" min="0" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enterprise Plan Price (cents)</th>
                    <td>
                        <input type="number"
                               name="mail4u_price_enterprise"
                               value="<?php echo esc_attr( get_option( 'mail4u_price_enterprise', '19900' ) ); ?>"
                               class="small-text" min="0" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function mail4u_campaigns_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'mail4u_campaigns';

    // Handle status update
    if (
        isset( $_POST['mail4u_update_status'], $_POST['campaign_id'], $_POST['new_status'] )
        && wp_verify_nonce( $_POST['_m4u_admin_nonce'] ?? '', 'mail4u_admin_status' )
    ) {
        $campaign_id = absint( $_POST['campaign_id'] );
        $new_status  = sanitize_key( $_POST['new_status'] );
        $allowed     = [ 'pending', 'active', 'completed', 'cancelled' ];
        if ( in_array( $new_status, $allowed, true ) ) {
            $wpdb->update( $table, [ 'status' => $new_status ], [ 'id' => $campaign_id ], [ '%s' ], [ '%d' ] );
            echo '<div class="notice notice-success is-dismissible"><p>Status updated.</p></div>';
        }
    }

    $campaigns = $wpdb->get_results( "SELECT c.*, u.user_email FROM {$table} c LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID ORDER BY c.created_at DESC" );
    ?>
    <div class="wrap">
        <h1>Mail4U — Campaigns</h1>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Industry</th><th>Deal Type</th><th>Status</th><th>Submitted</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $campaigns ) ) : ?>
                    <tr><td colspan="7">No campaigns yet.</td></tr>
                <?php else : ?>
                    <?php foreach ( $campaigns as $c ) : ?>
                        <tr>
                            <td><?php echo absint( $c->id ); ?></td>
                            <td><?php echo esc_html( $c->user_email ); ?></td>
                            <td><?php echo esc_html( $c->industry ); ?></td>
                            <td><?php echo esc_html( $c->deal_type ); ?></td>
                            <td><?php echo esc_html( ucfirst( $c->status ) ); ?></td>
                            <td><?php echo esc_html( $c->created_at ); ?></td>
                            <td>
                                <form method="post" style="display:inline-flex;gap:4px;">
                                    <?php wp_nonce_field( 'mail4u_admin_status', '_m4u_admin_nonce' ); ?>
                                    <input type="hidden" name="campaign_id" value="<?php echo absint( $c->id ); ?>" />
                                    <select name="new_status">
                                        <?php foreach ( [ 'pending', 'active', 'completed', 'cancelled' ] as $s ) : ?>
                                            <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $c->status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="mail4u_update_status" class="button button-small">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
