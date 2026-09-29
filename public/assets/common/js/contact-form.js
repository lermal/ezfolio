/**
 * Contact form shared by all portfolio themes.
 * Expects a form with id "contact-me-form" and window.ezfolioContactForm
 * ({ url, messages }) printed by resources/views/frontend/layouts/theme.blade.php.
 */
(function ($) {
    'use strict';

    var config = window.ezfolioContactForm || {};
    var messages = config.messages || {};

    function notify(message, type) {
        iziToast.show({
            title: '',
            message: message,
            messageSize: 12,
            position: 'topRight',
            theme: 'dark',
            pauseOnHover: true,
            timeout: 5000,
            progressBarColor: type === 'success' ? '#00ffb8' : '#ffafb4',
            color: '#565c70',
            messageColor: type === 'success' ? '#00ffb8' : '#ffafb4',
            icon: type === 'success' ? 'fas fa-check' : 'fas fa-times-circle'
        });
    }

    function errorMessages(jqXHR) {
        if (jqXHR.status === 0) {
            return [messages.networkError];
        }

        var response = jqXHR.responseJSON || {};
        var result = [];

        if (response.payload && typeof response.payload === 'object') {
            $.each(response.payload, function (field, errors) {
                result = result.concat(errors);
            });
        }

        if (!result.length) {
            result.push(response.message || messages.failed);
        }

        return result;
    }

    // Turnstile tokens are single-use, so the widget must issue a new one after every submit attempt.
    function resetCaptcha(form) {
        var widget = form.find('.cf-turnstile').get(0);

        if (widget && window.turnstile) {
            window.turnstile.reset(widget);
        }
    }

    function setSending(button, sending) {
        var isInput = button.is('input');

        if (sending) {
            button.data('original-content', isInput ? button.val() : button.html());
            button.prop('disabled', true);

            if (isInput) {
                button.val(messages.sending);
            } else {
                button.html('<i class="fas fa-spinner fa-spin"></i> ' + messages.sending);
            }
        } else {
            button.prop('disabled', false);

            if (isInput) {
                button.val(button.data('original-content'));
            } else {
                button.html(button.data('original-content'));
            }
        }
    }

    $(function () {
        var form = $('#contact-me-form');

        if (!form.length) {
            return;
        }

        form.validate({
            rules: {
                name: { required: true },
                email: { required: true, email: true },
                subject: { required: true },
                body: { required: true }
            },
            submitHandler: function (formElement) {
                var button = form.find('[type="submit"]');

                setSending(button, true);

                $.ajax({
                    url: config.url,
                    type: 'post',
                    dataType: 'json',
                    data: form.serialize(),
                    success: function () {
                        notify(messages.sent, 'success');
                        formElement.reset();
                    },
                    error: function (jqXHR) {
                        $.each(errorMessages(jqXHR), function (index, message) {
                            notify(message, 'error');
                        });
                    },
                    complete: function () {
                        resetCaptcha(form);
                        setSending(button, false);
                    }
                });
            }
        });
    });
})(jQuery);
