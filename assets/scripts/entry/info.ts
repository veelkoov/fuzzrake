import Static from '../Static';

grecaptcha.ready((): void => {
    grecaptcha.execute(Static.getGoogleRecaptchaSiteKey(), { action: 'info_emailHtml' }).then((token: string): void => {
        jQuery.ajax(Static.getApiUrl('info/email.part.html?token=' + token), {
            success: (data: string): void => {
                jQuery('#protected-contact-info').html(data);
            },
        });
    });
});
