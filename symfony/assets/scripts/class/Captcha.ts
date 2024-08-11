import DarnIt from '../DarnIt'

declare global {
    interface Window { formRecaptchaValidationCallback: (token: string) => void; }
}

export default class Captcha {
    public static setupOnForm(formSelector: string): void {
        const $form = jQuery(formSelector);

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

    private static setupValidationCallback($form: JQuery): void {
        const $_form = $form;

        window['formRecaptchaValidationCallback'] = function (token: string): void {
            try {
                const $tokenField = jQuery('#form_recaptcha_token');

                if (1 !== $tokenField.length) {
                    DarnIt.report('Token field has not been found.', null, false);
                    return;
                }

                $tokenField.val(token);

                const $form = $tokenField.parents('form');

                if (1 !== $form.length) {
                    DarnIt.report('Token field\'s form has not been found.', null, false);
                    return;
                }

                $_form.trigger('submit', [true]);
            } catch (exception) {
                DarnIt.report('Automatic captcha failed for unknown reason.', exception, false);
            }
        };
    }
}
