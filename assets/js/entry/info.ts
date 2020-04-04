'use strict';

import DataBridge from "../class/DataBridge";
import SuccessTextStatus = JQuery.Ajax.SuccessTextStatus;
import jqXHR = JQuery.jqXHR;

grecaptcha.ready((): void => {
    grecaptcha.execute(DataBridge.getGoogleRecaptchaSiteKey(), { action: 'info_emailHtml' }).then((token: string): void => {
        $.ajax(DataBridge.getApiUrl('info/email.part.html?token=' + token), {
            success: (data: any, status: SuccessTextStatus, jqXHR: jqXHR): void => {
                $('#protected-contact-info').html(data);
            },
        });
    });
});
