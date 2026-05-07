<?php defined( 'ABSPATH' ) || exit; ?>

<div class="m4u-wrap m4u-auth">

    <?php if ( ! empty( $notice ) ) echo $notice; ?>

    <div class="m4u-auth__panels">

        <!-- Registration panel -->
        <div class="m4u-auth__panel">
            <h2>Create Your Account</h2>
            <p class="m4u-auth__sub">Free to start — no credit card required.</p>
            <form method="post" class="m4u-form" id="m4u-register-form" novalidate>
                <?php wp_nonce_field( 'mail4u_register', '_m4u_reg_nonce' ); ?>
                <div class="m4u-form__group">
                    <label for="reg_username">Username</label>
                    <input type="text" id="reg_username" name="reg_username" required
                           autocomplete="username" placeholder="yourname" />
                </div>
                <div class="m4u-form__group">
                    <label for="reg_email">Email Address</label>
                    <input type="email" id="reg_email" name="reg_email" required
                           autocomplete="email" placeholder="you@company.com" />
                </div>
                <div class="m4u-form__group">
                    <label for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="reg_password" required
                           autocomplete="new-password" minlength="8" placeholder="Min. 8 characters" />
                </div>
                <div class="m4u-form__group">
                    <label for="reg_confirm">Confirm Password</label>
                    <input type="password" id="reg_confirm" name="reg_confirm" required
                           autocomplete="new-password" minlength="8" placeholder="Repeat password" />
                </div>
                <button type="submit" name="mail4u_register" class="m4u-btn m4u-btn--primary m4u-btn--full">
                    Create Account
                </button>
                <p class="m4u-form__legal">
                    By registering you agree to our <a href="#">Terms of Service</a>
                    and <a href="#">Privacy Policy</a>.
                </p>
            </form>
        </div>

        <div class="m4u-auth__divider"><span>or</span></div>

        <!-- Login panel -->
        <div class="m4u-auth__panel">
            <h2>Log In</h2>
            <p class="m4u-auth__sub">Welcome back. Pick up where you left off.</p>
            <form method="post" class="m4u-form" id="m4u-login-form">
                <?php wp_nonce_field( 'mail4u_login', '_m4u_log_nonce' ); ?>
                <div class="m4u-form__group">
                    <label for="login_username">Username</label>
                    <input type="text" id="login_username" name="login_username" required
                           autocomplete="username" placeholder="yourname" />
                </div>
                <div class="m4u-form__group">
                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="login_password" required
                           autocomplete="current-password" placeholder="Your password" />
                </div>
                <button type="submit" name="mail4u_login" class="m4u-btn m4u-btn--outline m4u-btn--full">
                    Log In
                </button>
                <p class="m4u-form__forgot">
                    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Forgot your password?</a>
                </p>
            </form>
        </div>

    </div>
</div>
