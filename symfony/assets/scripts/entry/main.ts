import "../../3rd-party/flag-icon-css/css/flag-icon.css";
import "../../styles/main.scss";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import Checklist from "../main/Checklist";
import ColumnsManager from "../main/ColumnsManager";
import FiltersManager from "../main/FiltersManager";
import { makerIdHashRegexp } from "../consts";
import { requireJQ, toggle } from "../jQueryUtils";

import "htmx.org";

// @ts-expect-error I am incompetent and I don't care to learn frontend
global.jQuery = require("jquery");

jQuery(function openCreatorCardGivenCreatorIdInAnchor(): void {
  if (
    AgeAndSfwConfig.getInstance().getMakerMode() ||
    !window.location.hash.match(makerIdHashRegexp)
  ) {
    return;
  }

  const creatorId = window.location.hash.slice(1);
  const type = "htmx:configRequest";

  const listener = function (event: unknown): void {
    // Garbage, but safe?
    if (
      event instanceof Event &&
      "detail" in event &&
      event.detail instanceof Object &&
      "path" in event.detail &&
      "string" === typeof event.detail.path &&
      event.detail.path.includes("_______")
    ) {
      event.detail.path = event.detail.path.replace("_______", creatorId);
      document.body.removeEventListener(type, listener);
    }
  };
  document.body.addEventListener(type, listener);

  requireJQ("#open-creator-card-given-creator-id-anchor").trigger("click");
});

if (AgeAndSfwConfig.getInstance().getMakerMode()) {
  requireJQ("#creator-mode-banner").removeClass("d-none");
  requireJQ("#main-page-intro").addClass("d-none");
  jQuery(() => {
    requireJQ("#creator-mode-parameter-field").trigger("click");
  });
} else {
  (function setUpChecklist(): void {
    new Checklist();
  })();
}

(function setUpSpeciesFilter(): void {
  // Enable expanding subspecies using the ▶ button
  jQuery("#filters-modal-body .specie .toggle").on("click", function (): void {
    jQuery(this).parents(".specie").nextAll(".subspecies").first().toggle(250);
  });

  // Set up the "any of the descendants is selected" indicators
  jQuery("#filters-modal-body .specie input").on("change", function (): void {
    const $allParentSpecieDivs = jQuery(this)
      .parents(".subspecies")
      .prevAll(".specie");

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    $allParentSpecieDivs.each((_, div) => {
      const $subspeciesInputs = jQuery(div)
        .next("br")
        .next(".subspecies")
        .find("input");
      const $indicator = jQuery(div).find(".descendants-indicator");

      const anySubspecieSelected =
        $subspeciesInputs.filter((_, input) => input.checked).length > 0;

      $indicator.removeClass("d-none");
      toggle($indicator, anySubspecieSelected, 0);
    });
  });
})();

(function setUpAllNoneInvertLinks(): void {
  jQuery("#filters-modal-body .all-none-invert").on(
    "click",
    function (event): void {
      const $link = jQuery(event.target);

      let changeFunction: (prev: boolean) => boolean;

      if ($link.hasClass("all")) {
        changeFunction = (): boolean => true;
      } else if ($link.hasClass("none")) {
        changeFunction = (): boolean => false;
      } else {
        changeFunction = (prev: boolean): boolean => !prev;
      }

      const $inputs = $link.parents("fieldset").find("input:not(.special)");

      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      $inputs.each((_, element) => {
        if (element instanceof HTMLInputElement) {
          element.checked = changeFunction(element.checked);
        }
      });
      $inputs.eq(0).trigger("change"); // I would expect the above actions would trigger it, but that is not the case.
    },
  );
})();

(function setUpFiltersManager(): void {
  new FiltersManager();
})();

(function setUpColumnsManager(): void {
  new ColumnsManager("#creators-table", "#columns-visibility-links a");
})();
