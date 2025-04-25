import Checkbox from "../class/Checkbox";
import DynamicFields from "../class/fields/DynamicFields";
import DynamicRadio from "../class/fields/DynamicRadio";
import Radio from "../class/fields/Radio";
import { ADULTS, NO, NO_CONTACT_ALLOWED } from "../consts";
import { toggle } from "../jQueryUtils";

import "../../styles/iu_form.scss";
import LocalFormState from "../class/LocalFormState";
import error from "../ErrorMessage";

jQuery(() => {
  const dataHolder = jQuery("#iu-form-data");
  const creatorId = dataHolder.data("creator-id");
  const step = dataHolder.data("step");

  switch (step) {
    case "start":
      setup_start_page();
      break;

    case "data":
      setup_data_page(creatorId);
      break;

    case "confirmation":
      cleanup(creatorId);
      break;

    default:
      error("Page setup failed.")
        .withConsoleDetails(`Unknown step: '${step}'.`)
        .reportOnce();
  }
});

function cleanup(creatorId: string): void {
  LocalFormState.cleanup("iu_form", creatorId);
}

function setup_start_page(): void {
  const confirmAddingANewOne = new Radio(
    "iu_form[confirmAddingANewOne]",
    refresh_page,
  );
  const ensureStudioIsNotThereAlready = new DynamicRadio(
    "iu_form[ensureStudioIsNotThereAlready]",
    "#ensureStudioIsNotThereAlready",
    refresh_page,
    false,
  );
  const confirmUpdatingTheRightOne = new Radio(
    "iu_form[confirmUpdatingTheRightOne]",
    refresh_page,
  );
  const $addNewStudioInstead = jQuery("#addNewStudioInstead");
  const $findTheStudioToUpdate = jQuery("#findTheStudioToUpdate");
  const confirmYouAreTheCreator = new DynamicRadio(
    "iu_form[confirmYouAreTheCreator]",
    "#confirmYouAreTheCreator",
    refresh_page,
    false,
  );
  const $doNotFillTheForm = jQuery("#doNotFillTheForm");
  const confirmNoPendingUpdates = new DynamicRadio(
    "iu_form[confirmNoPendingUpdates]",
    "#confirmNoPendingUpdates",
    refresh_page,
    false,
  );
  const decisionOverPreviousUpdates = new DynamicRadio(
    "iu_form[decisionOverPreviousUpdates]",
    "#decisionOverPreviousUpdates",
    refresh_page,
    false,
  );
  const $howToProceedWithQueuedUpdates = jQuery(
    "#howToProceedWithQueuedUpdates",
  );
  const $rulesAndContinueButton = jQuery("#rulesAndContinueButton");

  function refresh_page(): void {
    ensureStudioIsNotThereAlready.toggle(confirmAddingANewOne.isVal("yes"));

    toggle(
      $addNewStudioInstead,
      confirmUpdatingTheRightOne.isVal("add-new-instead"),
    );

    toggle(
      $findTheStudioToUpdate,
      confirmAddingANewOne.isVal("no") ||
        ensureStudioIsNotThereAlready.isVal("found-old-studio") ||
        confirmUpdatingTheRightOne.isVal("update-other-one"),
    );

    confirmYouAreTheCreator.toggle(
      ensureStudioIsNotThereAlready.isVal("is-new-studio") ||
        confirmUpdatingTheRightOne.isVal("correct"),
    );

    toggle($doNotFillTheForm, confirmYouAreTheCreator.isVal("not-the-creator"));

    confirmNoPendingUpdates.toggle(
      confirmYouAreTheCreator.isVal("i-am-the-creator"),
    );

    decisionOverPreviousUpdates.toggle(
      confirmNoPendingUpdates.isVal("submission-pending"),
    );

    toggle(
      $howToProceedWithQueuedUpdates,
      decisionOverPreviousUpdates.isVal("can-not-be-cancelled"),
    );

    toggle(
      $rulesAndContinueButton,
      confirmNoPendingUpdates.isAnySelected() &&
        (!decisionOverPreviousUpdates.isAvailable() ||
          decisionOverPreviousUpdates.isVal("can-be-cancelled")),
    );
  }

  refresh_page();
}

function setup_data_page(creatorId: string): void {
  setup_date_field_automation();
  setup_age_section_automation();
  setup_password_and_contact_automation();

  const state = new LocalFormState("iu_form", creatorId);
  jQuery("#iu-form-start-time").html(state.getSaveDateTime());
  jQuery("#iu-form-reset-button").on("click", () => {
    if (confirm("Are you sure you want to discard all your changes?")) {
      state.reset();
      location.reload();
    }
  });
}

function setup_password_and_contact_automation(): void {
  const $forgottenPassHint = jQuery("#forgotten_password_instructions");
  const $forgottenPassLabel = jQuery('label[for="iu_form_password"]');
  const $validationAcknowledgement = jQuery("#verification_acknowledgement");

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const changePasswordCheckbox = new Checkbox("iu_form_changePassword", (_) => {
    refresh();
  });

  const $contactLevelProsCons = jQuery(".pros-cons-contact-options");
  const contactAllowed = new Radio("iu_form[contactAllowed]", refresh);
  const emailAddressField = new DynamicFields(
    "#iu_form_emailAddress",
    "#email-address",
    true,
  );

  function refresh(immediate = false): void {
    const animationsDuration: JQuery.Duration = immediate ? 0 : "fast";

    const contactAllowedIdx = contactAllowed.selectedIdx();

    toggle(
      $contactLevelProsCons,
      function (idx, el): boolean {
        return (
          jQuery(el).data("min-level") <= contactAllowedIdx &&
          jQuery(el).data("max-level") >= contactAllowedIdx
        );
      },
      animationsDuration,
    );

    emailAddressField.toggle(
      contactAllowed.isAnySelected() &&
        !contactAllowed.isVal(NO_CONTACT_ALLOWED),
    );

    if ($forgottenPassHint.hasClass("d-none")) {
      $forgottenPassHint.removeClass("d-none");
      $forgottenPassHint.hide(0);
    }

    if (changePasswordCheckbox.isChecked) {
      $forgottenPassHint.show(animationsDuration);
      $forgottenPassLabel.text("Choose a new password");
    } else {
      $forgottenPassHint.hide(animationsDuration);
      $forgottenPassLabel.text("Updates password"); // grep-text-updates-password
    }

    toggle(
      $validationAcknowledgement,
      changePasswordCheckbox.isChecked &&
        ($validationAcknowledgement.hasClass("contact-was-not-allowed") ||
          contactAllowed.isVal(NO_CONTACT_ALLOWED)),
      animationsDuration,
    );
  }

  refresh(true);
}

function setup_date_field_automation(): void {
  const day = jQuery("#iu_form_since_day").hide();
  const month = jQuery("#iu_form_since_month").on("change", set_day);
  const year = jQuery("#iu_form_since_year").on("change", set_day);

  function set_day(): void {
    // Change value only if year&month are set; otherwise we'll get an error message if date's not set - unintentional requirement
    day.val(month.val() && year.val() ? "1" : ""); // grep-default-auto-since-day-01
  }
}

function setup_age_section_automation(): void {
  const doesNsfwField = new DynamicFields(
    'input[name="iu_form[doesNsfw]"]',
    "#doesNsfwContainer",
    true,
  );
  const worksWithMinorsField = new DynamicFields(
    'input[name="iu_form[worksWithMinors]"]',
    "#worksWithMinorsContainer",
    true,
  );

  const ages = new Radio("iu_form[ages]", refresh_age_section);
  const nsfwWebsite = new Radio("iu_form[nsfwWebsite]", refresh_age_section);
  const nsfwSocial = new Radio("iu_form[nsfwSocial]", refresh_age_section);
  const doesNsfw = new Radio("iu_form[doesNsfw]", refresh_age_section);

  function refresh_age_section(): void {
    doesNsfwField.toggle(ADULTS === ages.val());

    worksWithMinorsField.toggle(
      nsfwSocial.isVal(NO) &&
        nsfwWebsite.isVal(NO) &&
        (doesNsfw.isVal(NO) || !ages.isVal(ADULTS)),
    );
  }

  refresh_age_section();
}
