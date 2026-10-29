import "../../styles/creator_card.scss";
import MessageBus from "../class/MessageBus";
import { localizeDateTimes } from "../datetimes";
import Storage from "../class/Storage";

const LABELS_HIDDEN_STORAGE_KEY: string = "labels-explanations-hidden";
const LABELS_HIDING_BODY_CLASS: string = "hide-labels-explanations";

function setupLabelsExplanationsToggleButton(): void {
  if (Storage.getBoolean(LABELS_HIDDEN_STORAGE_KEY, true)) {
    jQuery("body").addClass(LABELS_HIDING_BODY_CLASS);
  }

  const toggler = jQuery("div.creator-card .header .toggle");
  toggler.addClass("toggling");
  toggler.on("click", function () {
    jQuery(this).parents("div.creator-card").toggleClass("expanded");
  });

  localizeDateTimes(jQuery(".creator-card"));

  jQuery(".labels-expl-toggle-btn").on("click", () => {
    const hiddenNow = jQuery("body")
      .toggleClass(LABELS_HIDING_BODY_CLASS)
      .hasClass(LABELS_HIDING_BODY_CLASS);

    Storage.saveBoolean(LABELS_HIDDEN_STORAGE_KEY, hiddenNow);
  });
}

// Setup on standalone pages
setupLabelsExplanationsToggleButton();

// Setup for main/list
MessageBus.listen(
  "event-creators-page-loaded",
  setupLabelsExplanationsToggleButton,
);
