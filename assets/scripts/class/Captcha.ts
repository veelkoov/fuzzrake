export default class Captcha {
    public static setupValidationCallback(): void {
        window['formRecaptchaValidationCallback'] = this.theCallback;
    }

    private static theCallback(token: string): void {
        try {
            const $tokenField = jQuery('#form_recaptcha_token');

            if (1 !== $tokenField.length) {
                this.error('Token field has not been found.');
                return;
            }

            $tokenField.val(token);

            const $form = $tokenField.parents('form');

            if (1 !== $form.length) {
                this.error('Token field\'s form has not been found.');
                return;
            }

            $form.trigger('submit');
        } catch (exception) {
            this.error(exception.toString());
        }
    };

    private static error(message: string): void {
        alert('ERROR! Sending form failed. ' + message);
    }
}
