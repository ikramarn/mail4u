<?php defined( 'ABSPATH' ) || exit; ?>

<div class="m4u-wrap m4u-dashboard">

    <!-- Dashboard header -->
    <div class="m4u-dashboard__header">
        <div>
            <h1>Welcome back, <?php echo esc_html( $user->display_name ); ?></h1>
            <p class="m4u-dashboard__email"><?php echo esc_html( $user->user_email ); ?></p>
        </div>
        <div class="m4u-dashboard__actions">
            <span class="m4u-badge m4u-badge--<?php echo esc_attr( $plan ); ?>">
                <?php echo esc_html( ucfirst( $plan ) ); ?> Plan
            </span>
            <?php if ( $plan === 'free' ) : ?>
                <a href="<?php echo esc_url( home_url( '/mail4u-pricing' ) ); ?>"
                   class="m4u-btn m4u-btn--primary m4u-btn--sm">Upgrade Plan</a>
            <?php endif; ?>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"
               class="m4u-btn m4u-btn--ghost m4u-btn--sm">Log Out</a>
        </div>
    </div>

    <?php if ( ! empty( $notice ) ) echo $notice; ?>

    <!-- New campaign form -->
    <section class="m4u-card">
        <h2>Launch a New Campaign</h2>
        <?php if ( $plan === 'free' ) : ?>
            <p class="m4u-notice m4u-info">
                Your free plan includes <strong>10 outreach emails</strong>.
                <a href="<?php echo esc_url( home_url( '/mail4u-pricing' ) ); ?>">Upgrade</a>
                for higher volume and priority delivery.
            </p>
        <?php endif; ?>
        <form method="post" class="m4u-form">
            <?php wp_nonce_field( 'mail4u_campaign', '_m4u_camp_nonce' ); ?>
            <div class="m4u-form__row">
                <div class="m4u-form__group">
                    <label for="industry">Target Industry</label>
                    <input type="text" id="industry" name="industry" required
                           placeholder="e.g. SaaS, Restaurant, E-commerce" />
                </div>
                <div class="m4u-form__group">
                    <label for="deal_type">Deal / Service Type</label>
                    <input type="text" id="deal_type" name="deal_type" required
                           placeholder="e.g. Web Design, Accounting, Legal" />
                </div>
            </div>
            <div class="m4u-form__group">
                <label for="message">Outreach Message Template</label>
                <textarea id="message" name="message" rows="7" required
                    placeholder="Write the email body to send on your behalf. Use [Business Name] as a dynamic placeholder for the recipient's company name."></textarea>
                <p class="m4u-form__hint">Tip: keep it concise, personalised, and focused on value. 3–5 sentences works best.</p>
            </div>
            <button type="submit" name="mail4u_campaign" class="m4u-btn m4u-btn--primary">
                Submit Campaign
            </button>
        </form>
    </section>

    <!-- Campaign history -->
    <section class="m4u-card">
        <h2>Your Campaigns</h2>
        <?php if ( empty( $campaigns ) ) : ?>
            <p class="m4u-empty">No campaigns yet. Submit your first one above to start generating leads.</p>
        <?php else : ?>
            <div class="m4u-table-wrap">
                <table class="m4u-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Industry</th>
                            <th>Deal Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $campaigns as $c ) : ?>
                            <tr>
                                <td><?php echo absint( $c->id ); ?></td>
                                <td><?php echo esc_html( $c->industry ); ?></td>
                                <td><?php echo esc_html( $c->deal_type ); ?></td>
                                <td>
                                    <span class="m4u-status m4u-status--<?php echo esc_attr( $c->status ); ?>">
                                        <?php echo esc_html( ucfirst( $c->status ) ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $c->created_at ) ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</div>
