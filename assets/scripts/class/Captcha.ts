import * as assert from "assert";

export default class Captcha {
    public static setupOnForm(formSelector: string): void {
        const $form = $(formSelector);

        if (1 !== $form.length) {
            console.error(`Failed to match form selector: '${formSelector}'`);
        }

        this.setupValidationCallback($form);

        $form.on('submit', function (event, captchaOK): void {
            if (!captchaOK) {
                event.preventDefault();
                grecaptcha.execute();
            }
        });
    }

    private static setupValidationCallback($form): void {
        const $_form = $form;

        window['formRecaptchaValidationCallback'] = function (token: string): void {
            try {
                const $tokenField = jQuery('#form_recaptcha_token');

                if (1 !== $tokenField.length) {
                    Captcha.error('Token field has not been found.');
                    return;
                }

                $tokenField.val(token);

                const $form = $tokenField.parents('form');

                if (1 !== $form.length) {
                    Captcha.error('Token field\'s form has not been found.');
                    return;
                }

                $_form.trigger('submit', [true]);
            } catch (exception) {
                Captcha.error(exception.toString());
            }
        };
    }

    private static error(message: string): void {
        alert('ERROR! Sending form failed. ' + message);
    }
}
