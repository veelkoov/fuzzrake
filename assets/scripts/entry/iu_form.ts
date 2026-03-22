import DynamicFields from "../class/fields/DynamicFields";
import DynamicRadio from "../class/fields/DynamicRadio";
import Radio from "../class/fields/Radio";
import { ADULTS, NO } from "../consts";
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
    confirmNoPendingUpdates.toggle(true); // FIXME

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

  const state = new LocalFormState("iu_form", creatorId);
  jQuery("#iu-form-start-time").html(state.getSaveDateTime());
  jQuery("#iu-form-reset-button").on("click", () => {
    if (confirm("Are you sure you want to discard all your changes?")) {
      state.reset();
      location.reload();
    }
  });
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
