import Radio from "../class/fields/Radio";
import { toggle } from "../jQueryUtils";

import "../../styles/user.scss";

jQuery(() => {
  const contactLevelProsCons = jQuery(".pros-cons-contact-options");
  const contactAllowed = new Radio("contact_form[contactAllowed]", refresh);

  function refresh(immediate = false): void {
    const animationsDuration: JQuery.Duration = immediate ? 0 : "fast";

    const contactAllowedIdx = contactAllowed.selectedIdx();

    const visibleFunc = function (_: number, htmlElement: JQuery): boolean {
      const element = jQuery(htmlElement);

      return (
        element.data("min-level") <= contactAllowedIdx &&
        contactAllowedIdx <= element.data("max-level")
      );
    };

    toggle(contactLevelProsCons, visibleFunc, animationsDuration);
  }

  refresh(true);
});
