import "../../styles/creator_card.scss";
import MessageBus from "../class/MessageBus";
import { localizeDateTimes } from "../datetimes";

MessageBus.listen("creators-page-loaded", () => {
  const toggler = jQuery("div.creator-card .header .toggle");
  toggler.addClass("toggling");
  toggler.on("click", function () {
    jQuery(this).parents("div.creator-card").toggleClass("expanded");
  });

  localizeDateTimes(jQuery(".creator-card"));
});
