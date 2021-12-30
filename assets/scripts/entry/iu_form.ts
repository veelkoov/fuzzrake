import {NO_CONTACT_ALLOWED} from "../consts";
import {Radio} from "../class/Radio";
import {toggle} from "../jQueryUtils";

require('../../styles/iu_form.less');

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

function display_password_change_hint_if_checked_forgot(): void {
    jQuery('#iu_form_changePassword').on('change', (evt) => {
        jQuery('#forgotten_password_instructions')
            .removeClass('d-none')
            .toggle($(evt.target).is(':checked'));
    }).trigger('change');
}

function hide_contact_form_part_if_no_contact_allowed(): void {
    const $contactInfoContainer = jQuery('#contact_info');
    const $contactInfoObfuscatedField = jQuery('#iu_form_contactInfoObfuscated');

    const contactAllowed = new Radio('iu_form[contactAllowed]', refresh);

    function refresh(): void {
        let requireContactInfo = contactAllowed.isAnySelected() && !contactAllowed.isVal(NO_CONTACT_ALLOWED);

        $contactInfoObfuscatedField.prop('required', requireContactInfo);
        toggle($contactInfoContainer, requireContactInfo);
    }

    refresh();
}

let day = jQuery('#iu_form_since_day').hide();
let month = jQuery('#iu_form_since_month').on('change', set_day);
let year = jQuery('#iu_form_since_year').on('change', set_day);
