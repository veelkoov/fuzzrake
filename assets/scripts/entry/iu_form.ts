import DataBridge from "../class/DataBridge";
import SuccessTextStatus = JQuery.Ajax.SuccessTextStatus;
import jqXHR = JQuery.jqXHR;
import ErrorTextStatus = JQuery.Ajax.ErrorTextStatus;
import {NO_CONTACT_ALLOWED, ADULTS, NO} from "../consts";
import {Radio} from "../class/Radio";
import {toggle} from "../jQueryUtils";

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
    setup_date_field_automation();
    setup_age_section_automation();
});


function display_password_change_hint_if_checked_forgot(): void {
    jQuery('#iu_form_changePassword').on('change', (evt) => {
        jQuery('#forgotten_password_instructions')
            .removeClass('d-none')
            .toggle($(evt.target).is(':checked'));
    }).trigger('change');
}

function hide_contact_form_part_if_no_contact_allowed(): void {
    jQuery('input[type=radio][name="iu_form[contactAllowed]"]').on('change', (evt) => {
        let is_contact_allowed = NO_CONTACT_ALLOWED !== jQuery(evt.target).val();

        jQuery('#contact_info').toggle(is_contact_allowed);
        jQuery('#iu_form_contactInfoObfuscated').prop('required', is_contact_allowed);
    });
}

function setup_date_field_automation(): void {
    const day = jQuery('#iu_form_since_day').hide();
    const month = jQuery('#iu_form_since_month').on('change', set_day);
    const year = jQuery('#iu_form_since_year').on('change', set_day);

    function set_day(): void {
        // Change value only if year&month are set; otherwise we'll get an error message if date's not set - unintentional requirement
        day.val(month.val() && year.val() ? '1' : ''); // grep-default-auto-since-day-01
    }
}

function setup_age_section_automation(): void {
    const $doesNsfwContainer = jQuery('#doesNsfwContainer');
    const $worksWithMinorsContainer = jQuery('#worksWithMinorsContainer');

    const ages = new Radio('iu_form[ages]', refresh_age_section);
    const nsfwWebsite = new Radio('iu_form[nsfwWebsite]', refresh_age_section);
    const nsfwSocial = new Radio('iu_form[nsfwSocial]', refresh_age_section);
    const doesNsfw = new Radio('iu_form[doesNsfw]', refresh_age_section);

    function refresh_age_section(): void {
        toggle($doesNsfwContainer, ADULTS === ages.val());

        toggle($worksWithMinorsContainer, nsfwSocial.isVal(NO) && nsfwWebsite.isVal(NO)
            && (doesNsfw.isVal(NO) || !ages.isVal(ADULTS)));
    }

    refresh_age_section();
}
