import DataBridge from "../class/DataBridge";
import SuccessTextStatus = JQuery.Ajax.SuccessTextStatus;
import jqXHR = JQuery.jqXHR;

grecaptcha.ready((): void => {
    grecaptcha.execute(DataBridge.getGoogleRecaptchaSiteKey(), { action: 'info_emailHtml' }).then((token: string): void => {
        jQuery.ajax(DataBridge.getApiUrl('info/email.part.html?token=' + token), {
            success: (data: any, status: SuccessTextStatus, jqXHR: jqXHR): void => {
                jQuery('#protected-contact-info').html(data);
            },
        });
    });
});
