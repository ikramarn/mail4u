<?php defined( 'ABSPATH' ) || exit; ?>

<div class="m4u-wrap m4u-contact">

    <div class="m4u-contact__header">
        <h1>Contact Us</h1>
        <p>Have a question, need help with a mailing campaign, or want to discuss an enterprise plan? We’d love to hear from you.</p>
    </div>

    <div class="m4u-contact__grid">

        <!-- Info column -->
        <div class="m4u-contact__info">
            <div class="m4u-contact__info-block">
                <h3>Get in Touch</h3>
                <ul>
                    <li><strong>Email:</strong> support@mail4u.com</li>
                    <li><strong>Hours:</strong> Mon &ndash; Fri, 9 AM &ndash; 6 PM GMT</li>
                    <li><strong>Response time:</strong> Within 24 hours</li>
                </ul>
            </div>
            <div class="m4u-contact__info-block">
                <h3>How Can We Help?</h3>
                <ul>
                    <li>Mailing campaign setup and strategy</li>
                    <li>Billing and payment questions</li>
                    <li>Print quality or dispatch issues</li>
                    <li>Custom enterprise postal plans</li>
                    <li>Partnership enquiries</li>
                </ul>
            </div>
        </div>

        <!-- Form column -->
        <div class="m4u-contact__form-wrap">
            <?php if ( ! empty( $notice ) ) echo $notice; ?>
            <form method="post" class="m4u-form" id="m4u-contact-form">
                <?php wp_nonce_field( 'mail4u_contact', '_m4u_ctct_nonce' ); ?>
                <div class="m4u-form__row">
                    <div class="m4u-form__group">
                        <label for="contact_name">Your Name <span class="m4u-req">*</span></label>
                        <input type="text" id="contact_name" name="contact_name" required
                               autocomplete="name" placeholder="Jane Smith" />
                    </div>
                    <div class="m4u-form__group">
                        <label for="contact_email">Email Address <span class="m4u-req">*</span></label>
                        <input type="email" id="contact_email" name="contact_email" required
                               autocomplete="email" placeholder="jane@company.com" />
                    </div>
                </div>
                <div class="m4u-form__group">
                    <label for="contact_subject">Subject</label>
                    <input type="text" id="contact_subject" name="contact_subject"
                           placeholder="e.g. Question about Pro plan" />
                </div>
                <div class="m4u-form__group">
                    <label for="contact_message">Message <span class="m4u-req">*</span></label>
                    <textarea id="contact_message" name="contact_message" rows="6" required
                              placeholder="Tell us what you need and we'll get back to you shortly."></textarea>
                </div>
                <button type="submit" name="mail4u_contact" class="m4u-btn m4u-btn--primary">
                    Send Message
                </button>
            </form>
        </div>

    </div>
</div>
