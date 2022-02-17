import {ADULTS, NO, NO_CONTACT_ALLOWED} from "../consts";
import Radio from "../class/Radio";
import {toggle} from "../jQueryUtils";
import RequiredField from "../class/RequiredField";

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
    react_to_contact_allowance_changes();
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

function react_to_contact_allowance_changes(): void {
    const $prosCons = jQuery('.pros-cons-contact-options');

    const contactAllowed = new Radio('iu_form[contactAllowed]', refresh);
    const contactInfoField = new RequiredField('#iu_form_contactInfoObfuscated', '#contact_info');

    function refresh(immediate: boolean = false): void {
        contactInfoField.setRequired(contactAllowed.isAnySelected() && !contactAllowed.isVal(NO_CONTACT_ALLOWED));

        let duration: JQuery.Duration = immediate ? 0 : 'fast';
        let level = contactAllowed.selectedIdx();

        toggle($prosCons, function (idx, el): boolean {
            return $(el).data('min-level') <= level
                && $(el).data('max-level') >= level;
        }, duration);
    }

    refresh(true);
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
    const doesNsfwField = new RequiredField('input[name="iu_form[doesNsfw]"]', '#doesNsfwContainer');
    const worksWithMinorsField = new RequiredField('input[name="iu_form[worksWithMinors]"]', '#worksWithMinorsContainer');

    const ages = new Radio('iu_form[ages]', refresh_age_section);
    const nsfwWebsite = new Radio('iu_form[nsfwWebsite]', refresh_age_section);
    const nsfwSocial = new Radio('iu_form[nsfwSocial]', refresh_age_section);
    const doesNsfw = new Radio('iu_form[doesNsfw]', refresh_age_section);

    function refresh_age_section(): void {
        doesNsfwField.setRequired(ADULTS === ages.val());

        worksWithMinorsField.setRequired(nsfwSocial.isVal(NO) && nsfwWebsite.isVal(NO)
            && (doesNsfw.isVal(NO) || !ages.isVal(ADULTS)));
    }

    refresh_age_section();
}
