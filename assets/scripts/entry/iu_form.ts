import DataBridge from "../class/DataBridge";
import SuccessTextStatus = JQuery.Ajax.SuccessTextStatus;
import jqXHR = JQuery.jqXHR;
import ErrorTextStatus = JQuery.Ajax.ErrorTextStatus;

require('../../styles/iu_form.less');

grecaptcha.ready((): void => {
    grecaptcha.execute(DataBridge.getGoogleRecaptchaSiteKey(), { action: 'iu_form_verify' }).then((token: string): void => {
        jQuery.ajax(DataBridge.getApiUrl('iu_form/verify?token=' + token), {
            success: (data: any, status: SuccessTextStatus, _: jqXHR): void => {
                jQuery('#iu_form_container').show().removeClass('d-none');
            },
            error: (jqXHR1: jqXHR, textStatus: ErrorTextStatus, errorThrown: string): void => {
                alert('ERROR! Automatic captcha failed: ' + errorThrown);
            }
        });
    });
});

jQuery((_$: JQueryStatic) => {
    // @ts-ignore
    window.iuFormRecaptchaValidationCallback = function(token: string): void {
        try {
            jQuery('#iu_form_recaptcha_token').val(token).parents('form').trigger('submit');
        } catch (e) {
            alert('ERROR! Sending form failed. ' + e);
        }
    }
});

function set_day() {
    // Change value only if year&month are set; otherwise we'll get an error message if date's not set - unintentional requirement
    day.val(month.val() && year.val() ? '1' : ''); // grep-default-auto-since-day-01
}

let day = jQuery('#iu_form_since_day').hide();
let month = jQuery('#iu_form_since_month').on('change', set_day);
let year = jQuery('#iu_form_since_year').on('change', set_day);
