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
                alert(textStatus + ': ' + errorThrown);
            }
        });
    });
});

jQuery(($: JQueryStatic) => {
    // @ts-ignore
    window.iuFormRecaptchaValidationCallback = function(token: string): void {
        try {
            jQuery('#iu_form_recaptcha_token').val(token).parents('form').trigger('submit');
        } catch (e) {
            alert('ERROR! Sending form failed. ' + e);
        }
    }

    $('#iu_form_since_day').hide().val('1'); // grep-default-auto-since-day-01
});
