import DataBridge from '../class/DataBridge';

grecaptcha.ready((): void => {
    grecaptcha.execute(DataBridge.getGoogleRecaptchaSiteKey(), { action: 'info_emailHtml' }).then((token: string): void => {
        jQuery.ajax(DataBridge.getApiUrl('info/email.part.html?token=' + token), {
            success: (data: any, _: JQuery.Ajax.SuccessTextStatus, __: JQuery.jqXHR): void => {
                jQuery('#protected-contact-info').html(data);
            },
        });
    });
});
