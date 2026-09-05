import "../../styles/main.scss";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import Checklist from "../main/Checklist";
import ColumnsManager from "../main/ColumnsManager";
import FiltersManager from "../main/FiltersManager";
import { creatorIdHashRegexp } from "../consts";
import { requireJQ, toggle } from "../jQueryUtils";
import MessageBus from "../class/MessageBus";

jQuery(function openCreatorCardGivenCreatorIdInAnchor(): void {
  if (
    // Before 2026-09-05 cards were opened with such anchors
    !AgeAndSfwConfig.getInstance().getCreatorMode() &&
    window.location.hash.match(creatorIdHashRegexp)
  ) {
    const location = window.location;
    const creatorId = location.hash.slice(1);
    const newUrl = `${location.protocol}//${location.host}/c/${creatorId}`; // grep-code-creator-card-path

    window.location.replace(newUrl);
  }
});

if (AgeAndSfwConfig.getInstance().getCreatorMode()) {
  jQuery(() => {
    requireJQ("#creator-mode-banner").removeClass("d-none");
    requireJQ("#main-page-intro").addClass("d-none");
    requireJQ("#creator-mode-parameter-field").val("1");
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
  new ColumnsManager("#main-creators-list", "#pref-fltr-offcanvas .colvis");
})();

// @ts-expect-error I am incompetent, and I don't care to learn frontend
window.goToPage = function (pageNumber: number): void {
  requireJQ("#page-number").val(pageNumber).trigger("click");
};

(function setUpPreCachingOfNextCreatorsPage(): void {
  document.body.addEventListener("htmx:afterRequest", (event: Event): void => {
    if (!(event instanceof CustomEvent)) {
      return;
    }

    const currentPagePath = event.detail.pathInfo.finalRequestPath;
    const match = currentPagePath.match(/[?&]page=(\d+)/); // grep-code-page-number-parameter-name

    if (match != null) {
      const currentPageParameter = match[0];
      const currentPageNumber = Number.parseInt(match[1]);

      const nextPageParameter = currentPageParameter.replace(
        currentPageNumber,
        currentPageNumber + 1,
      );
      const nextPagePath = currentPagePath.replace(
        currentPageParameter,
        nextPageParameter,
      );

      jQuery.ajax({ url: nextPagePath }); // Ignore result, just cache it.
    }
  });
})();

MessageBus.listen("event-retrieved-userinfo", () => {
  const roles: string[] = jQuery("#userinfo").data("roles").split(",");

  if (roles.includes("ROLE_ADMIN")) {
    jQuery("body").addClass("user-is-admin");
  }
});
