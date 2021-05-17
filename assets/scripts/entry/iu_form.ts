import DataBridge from "../class/DataBridge";
import SuccessTextStatus = JQuery.Ajax.SuccessTextStatus;
import jqXHR = JQuery.jqXHR;
import ErrorTextStatus = JQuery.Ajax.ErrorTextStatus;
import {NO_CONTACT_ALLOWED} from "../consts";

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

    display_password_change_hint_if_checked_forgot();
    hide_contact_form_part_if_no_contact_allowed();
});

function set_day() {
    // Change value only if year&month are set; otherwise we'll get an error message if date's not set - unintentional requirement
    day.val(month.val() && year.val() ? '1' : ''); // grep-default-auto-since-day-01
}

function display_password_change_hint_if_checked_forgot() {
    jQuery('#forgotten_password').on('change', (evt) => {
        jQuery('#forgotten_password_instructions').toggle($(evt.target).is(':checked'));
    });
}

function hide_contact_form_part_if_no_contact_allowed() {
    jQuery('input[type=radio][name="iu_form[contactAllowed]"]').on('change', (evt) => {
        let is_contact_allowed = NO_CONTACT_ALLOWED !== jQuery(evt.target).val();

        jQuery('#contact_info').toggle(is_contact_allowed);
        jQuery('#iu_form_contactInfoObfuscated').prop('required', is_contact_allowed);
    });
}

let day = jQuery('#iu_form_since_day').hide();
let month = jQuery('#iu_form_since_month').on('change', set_day);
let year = jQuery('#iu_form_since_year').on('change', set_day);
