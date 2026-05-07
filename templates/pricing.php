<?php defined( 'ABSPATH' ) || exit; ?>

<div class="m4u-wrap">

    <section class="m4u-pricing-hero">
        <h1>Simple, Transparent Pricing</h1>
        <p>All plans include a fully managed outreach campaign service. Upgrade or cancel anytime.</p>
    </section>

    <?php if ( ! empty( $notice ) ) echo $notice; ?>

    <section class="m4u-pricing-grid">

        <!-- Starter -->
        <div class="m4u-plan">
            <div class="m4u-plan__header">
                <h2 class="m4u-plan__name">Starter</h2>
                <p class="m4u-plan__price"><span>$29</span><em>/mo</em></p>
                <p class="m4u-plan__tagline">Perfect for freelancers and solo operators.</p>
            </div>
            <ul class="m4u-plan__features">
                <li class="yes">500 outreach emails / month</li>
                <li class="yes">Up to 5 active campaigns</li>
                <li class="yes">Standard delivery (48 hrs)</li>
                <li class="yes">Email support</li>
                <li class="no">Priority delivery</li>
                <li class="no">Dedicated account manager</li>
            </ul>
            <form method="post">
                <?php wp_nonce_field( 'mail4u_buy_plan', '_m4u_plan_nonce' ); ?>
                <input type="hidden" name="mail4u_plan" value="starter" />
                <button type="submit" name="mail4u_buy_plan" class="m4u-btn m4u-btn--outline m4u-btn--full">
                    Get Starter
                </button>
            </form>
        </div>

        <!-- Pro (featured) -->
        <div class="m4u-plan m4u-plan--featured">
            <div class="m4u-plan__badge">Most Popular</div>
            <div class="m4u-plan__header">
                <h2 class="m4u-plan__name">Pro</h2>
                <p class="m4u-plan__price"><span>$79</span><em>/mo</em></p>
                <p class="m4u-plan__tagline">For growing teams and agencies.</p>
            </div>
            <ul class="m4u-plan__features">
                <li class="yes">2,000 outreach emails / month</li>
                <li class="yes">Up to 20 active campaigns</li>
                <li class="yes">Priority delivery (24 hrs)</li>
                <li class="yes">Email &amp; chat support</li>
                <li class="yes">Campaign analytics</li>
                <li class="no">Dedicated account manager</li>
            </ul>
            <form method="post">
                <?php wp_nonce_field( 'mail4u_buy_plan', '_m4u_plan_nonce' ); ?>
                <input type="hidden" name="mail4u_plan" value="pro" />
                <button type="submit" name="mail4u_buy_plan" class="m4u-btn m4u-btn--primary m4u-btn--full">
                    Get Pro
                </button>
            </form>
        </div>

        <!-- Enterprise -->
        <div class="m4u-plan">
            <div class="m4u-plan__header">
                <h2 class="m4u-plan__name">Enterprise</h2>
                <p class="m4u-plan__price"><span>$199</span><em>/mo</em></p>
                <p class="m4u-plan__tagline">Unlimited volume for established businesses.</p>
            </div>
            <ul class="m4u-plan__features">
                <li class="yes">Unlimited outreach emails</li>
                <li class="yes">Unlimited campaigns</li>
                <li class="yes">Same-day delivery</li>
                <li class="yes">Priority 24 / 7 support</li>
                <li class="yes">Full campaign analytics</li>
                <li class="yes">Dedicated account manager</li>
            </ul>
            <form method="post">
                <?php wp_nonce_field( 'mail4u_buy_plan', '_m4u_plan_nonce' ); ?>
                <input type="hidden" name="mail4u_plan" value="enterprise" />
                <button type="submit" name="mail4u_buy_plan" class="m4u-btn m4u-btn--outline m4u-btn--full">
                    Get Enterprise
                </button>
            </form>
        </div>

    </section>

    <!-- Free tier note -->
    <div class="m4u-pricing-free">
        <p>Not ready to commit? <a href="<?php echo esc_url( home_url( '/mail4u-register' ) ); ?>">Create a free account</a> and get <strong>10 outreach emails</strong> to test the platform — no card required.</p>
    </div>

    <!-- FAQ -->
    <section class="m4u-faq">
        <h2 class="m4u-section-title">Frequently Asked Questions</h2>
        <div class="m4u-faq__grid">
            <div class="m4u-faq__item">
                <h4>Do you write the email for me?</h4>
                <p>You provide the message template, we handle personalisation, targeting, and delivery. You keep full control of your brand voice.</p>
            </div>
            <div class="m4u-faq__item">
                <h4>How quickly do campaigns go live?</h4>
                <p>Standard plans go live within 48 hours. Pro plans within 24 hours. Enterprise campaigns launch the same day.</p>
            </div>
            <div class="m4u-faq__item">
                <h4>Can I cancel anytime?</h4>
                <p>Plans are billed monthly with no contracts. You can cancel before your next billing date at any time.</p>
            </div>
            <div class="m4u-faq__item">
                <h4>Are payments secure?</h4>
                <p>All payments are processed by Stripe, a PCI-DSS Level 1 certified provider. We never store your card details.</p>
            </div>
        </div>
    </section>

</div>
