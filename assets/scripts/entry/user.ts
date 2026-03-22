import Radio from "../class/fields/Radio";
import { toggle } from "../jQueryUtils";
import { markCompletedInit } from "../common";

import "../../styles/user.scss";

jQuery(() => {
  setupContactPermitProsCons();
  markCompletedInit();
});

function setupContactPermitProsCons() {
  const contactLevelProsCons = jQuery(".pros-cons-contact-options");
  const contactPermit = new Radio("contact_form[contactPermit]", refresh);

  function refresh(immediate = false): void {
    const animationsDuration: JQuery.Duration = immediate ? 0 : "fast";

    const contactPermitIdx = contactPermit.selectedIdx();

    const visibleFunc = function (_: number, htmlElement: JQuery): boolean {
      const element = jQuery(htmlElement);

      return (
        element.data("min-level") <= contactPermitIdx &&
        contactPermitIdx <= element.data("max-level")
      );
    };

    toggle(contactLevelProsCons, visibleFunc, animationsDuration);
  }

  refresh(true);
}
