import Captcha from '../class/Captcha';
import Checkbox from '../class/Checkbox';
import DynamicFields from '../class/fields/DynamicFields';
import DynamicRadio from '../class/fields/DynamicRadio';
import Radio from '../class/fields/Radio';
import {ADULTS, NO, NO_CONTACT_ALLOWED} from '../consts';
import {toggle} from '../jQueryUtils';

import '../../styles/iu_form.scss';

jQuery(() => {
    const caption = $('form[name="iu_form"] input[type="submit"]').val();

    switch (caption) {
        case 'Agree and continue':
            setup_start_page();
            break;

        case 'Continue':
            setup_data_page();
            break;

        case 'Submit':
            setup_password_and_contact_page();
            break;

        default:
            console.error(`Failed to detect I/U form submission stage: '${caption}'`);
    }
});

function setup_start_page(): void {
    Captcha.setupOnForm('form[name="iu_form"]');

    const confirmAddingANewOne = new Radio('iu_form[confirmAddingANewOne]', refresh_page);
    const ensureStudioIsNotThereAlready = new DynamicRadio('iu_form[ensureStudioIsNotThereAlready]', '#ensureStudioIsNotThereAlready', refresh_page, false);
    const confirmUpdatingTheRightOne = new Radio('iu_form[confirmUpdatingTheRightOne]', refresh_page);
    const $addNewStudioInstead = jQuery('#addNewStudioInstead');
    const $findTheStudioToUpdate = jQuery('#findTheStudioToUpdate');
    const confirmYouAreTheMaker = new DynamicRadio('iu_form[confirmYouAreTheMaker]', '#confirmYouAreTheMaker', refresh_page, false);
    const $doNotFillTheForm = jQuery('#doNotFillTheForm');
    const confirmNoPendingUpdates = new DynamicRadio('iu_form[confirmNoPendingUpdates]', '#confirmNoPendingUpdates', refresh_page, false);
    const decisionOverPreviousUpdates = new DynamicRadio('iu_form[decisionOverPreviousUpdates]', '#decisionOverPreviousUpdates', refresh_page, false);
    const $howToProceedWithQueuedUpdates = jQuery('#howToProceedWithQueuedUpdates');
    const $rulesAndContinueButton = jQuery('#rulesAndContinueButton');

    function refresh_page(): void {
        ensureStudioIsNotThereAlready.toggle(confirmAddingANewOne.isVal('yes'));

        toggle($addNewStudioInstead, confirmUpdatingTheRightOne.isVal('add-new-instead'));

        toggle($findTheStudioToUpdate,
            confirmAddingANewOne.isVal('no')
            || ensureStudioIsNotThereAlready.isVal('found-old-studio')
            || confirmUpdatingTheRightOne.isVal('update-other-one')
        );

        confirmYouAreTheMaker.toggle(
            ensureStudioIsNotThereAlready.isVal('is-new-studio')
            || confirmUpdatingTheRightOne.isVal('correct')
        );

        toggle($doNotFillTheForm, confirmYouAreTheMaker.isVal('not-the-maker'));

        confirmNoPendingUpdates.toggle(confirmYouAreTheMaker.isVal('i-am-the-maker'));

        decisionOverPreviousUpdates.toggle(confirmNoPendingUpdates.isVal('submission-pending'));

        toggle($howToProceedWithQueuedUpdates, decisionOverPreviousUpdates.isVal('can-not-be-cancelled'));

        toggle($rulesAndContinueButton,
            confirmNoPendingUpdates.isAnySelected()
            && (!decisionOverPreviousUpdates.isAvailable()
                || decisionOverPreviousUpdates.isVal('can-be-cancelled'))
        );
    }

    refresh_page();
}

function setup_data_page(): void {
    setup_date_field_automation();
    setup_age_section_automation();
}

function setup_password_and_contact_page(): void {
    react_to_contact_allowance_changes();
    display_password_change_hint_if_checked_forgot();
}

function display_password_change_hint_if_checked_forgot(): void {
    const $hint = jQuery('#forgotten_password_instructions');
    const $label = jQuery('label[for="iu_form_password"]');

    new Checkbox('iu_form_changePassword', (checkbox: Checkbox) => {
        if ($hint.hasClass('d-none')) {
            $hint.removeClass('d-none');
            $hint.hide(0);
        }

        if (checkbox.isChecked()) {
            $hint.show('fast');
            $label.text('Choose a new password');
        } else {
            $hint.hide('fast');
            $label.text('Updates password'); // grep-text-updates-password
        }
    });
}

function react_to_contact_allowance_changes(): void {
    const $prosCons = jQuery('.pros-cons-contact-options');

    const contactAllowed = new Radio('iu_form[contactAllowed]', refresh);
    const contactInfoField = new DynamicFields('#iu_form_contactInfoObfuscated', '#contact_info', true);

    function refresh(immediate: boolean = false): void {
        contactInfoField.toggle(contactAllowed.isAnySelected() && !contactAllowed.isVal(NO_CONTACT_ALLOWED));

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
    const doesNsfwField = new DynamicFields('input[name="iu_form[doesNsfw]"]', '#doesNsfwContainer', true);
    const worksWithMinorsField = new DynamicFields('input[name="iu_form[worksWithMinors]"]', '#worksWithMinorsContainer', true);

    const ages = new Radio('iu_form[ages]', refresh_age_section);
    const nsfwWebsite = new Radio('iu_form[nsfwWebsite]', refresh_age_section);
    const nsfwSocial = new Radio('iu_form[nsfwSocial]', refresh_age_section);
    const doesNsfw = new Radio('iu_form[doesNsfw]', refresh_age_section);

    function refresh_age_section(): void {
        doesNsfwField.toggle(ADULTS === ages.val());

        worksWithMinorsField.toggle(nsfwSocial.isVal(NO) && nsfwWebsite.isVal(NO)
            && (doesNsfw.isVal(NO) || !ages.isVal(ADULTS)));
    }

    refresh_age_section();
}
