import "bootstrap";
import * as moment from "moment";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import Visitor from "../class/Visitor";

import "../../styles/general.scss";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "bootstrap/dist/css/bootstrap.min.css";

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
  jQuery("a.disable-filters-goto-main-page").on("click", () => {
    const config = AgeAndSfwConfig.getInstance();

    config.setMakerMode(true);
    config.save();
  });
});

jQuery(() => {
  const newsNavlink = jQuery("#navlink-news");

  // If we're ON the events page, then never show a badge
  if (newsNavlink.hasClass("active")) {
    return;
  }

  // Validate that we have a timestamp for when events were last updated
  const timestampStr = newsNavlink.data("latest-event");
  if (!timestampStr) {
    return;
  }

  const timestamp = new Date(timestampStr);
  if (isNaN(timestamp.valueOf())) {
    return;
  }

  // If the user has never visited the events page, don't show the badge.
  // It would be annoying to show up to a new website for the first time and be
  // told you have unread notifications.
  const { lastVisitedEventsPage } = Visitor;
  if (!lastVisitedEventsPage) {
    return;
  }

  // If there've been no new events since they last visited, then don't display
  // the badge
  if (lastVisitedEventsPage.valueOf() >= timestamp.valueOf()) {
    return;
  }

  // There are new events. Show an unread badge
  const icon = newsNavlink.find("i");
  const badge = jQuery(document.createElement("div"));
  badge.addClass("unread-badge");
  icon.append(badge);
});
