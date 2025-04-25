import "bootstrap";
import * as moment from "moment";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";

import "../../styles/general.scss";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "bootstrap/dist/css/bootstrap.min.css";
import Navbar from "../class/navbar/Navbar";

// @ts-expect-error I am incompetent and I don't care to learn frontend
global.jQuery = jQuery;

jQuery(() => {
  jQuery("span.utc_datetime").each((index, element) => {
    const $span = jQuery(element);

    const parts = $span.text().match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}) UTC$/); // grep-expected-utc-datetime-format

    if (null === parts) {
      return;
    }

    $span.attr("title", $span.text());

    const originalIsoTime = `${parts[1]}T${parts[2]}:00Z`;

    $span.html(moment(originalIsoTime).local().format("YYYY-MM-DD HH:mm"));
  });
});

jQuery(() => {
  const config = AgeAndSfwConfig.getInstance();

  jQuery("a.disable-filters-goto-main-page").on("click", () => {
    config.setCreatorMode(true);
    config.save();
  });

  jQuery("#btn-reenable-filters").on("click", () => {
    // Does not prevent default (link navigation -> page reload) to display the checklist.
    // TODO: Optimize. See https://github.com/veelkoov/fuzzrake/issues/233
    config.setCreatorMode(false);
    config.save();
  });
});

jQuery(() => Navbar.init());
