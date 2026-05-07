/**
 * Mail4U — script.js
 * Minimal client-side enhancements.
 */
jQuery(function ($) {
    'use strict';

    /* ── Password confirmation validation ─────────────────────────────── */
    var $regForm = $('#m4u-register-form');
    if ($regForm.length) {
        $regForm.on('submit', function (e) {
            var pwd     = $('#reg_password').val();
            var confirm = $('#reg_confirm').val();
            if (pwd !== confirm) {
                e.preventDefault();
                showInlineError($regForm, 'Passwords do not match. Please try again.');
                $('#reg_confirm').focus();
            }
        });
    }

    /* ── Inline error helper ───────────────────────────────────────────── */
    function showInlineError($form, message) {
        $form.find('.m4u-js-error').remove();
        $('<p class="m4u-notice m4u-error m4u-js-error"></p>')
            .text(message)
            .prependTo($form);
    }

    /* ── Disable submit button on submit to prevent double-clicks ──────── */
    $('.m4u-form').on('submit', function () {
        var $btn = $(this).find('[type="submit"]');
        $btn.prop('disabled', true).css('opacity', '0.6');
    });
});
