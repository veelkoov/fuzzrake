import "../../styles/creator_card.scss";
import MessageBus from "../class/MessageBus";
import { localizeDateTimes } from "../datetimes";

MessageBus.listen("creators-page-loaded", () => {
  jQuery("div.creator-card button.toggle").on("click", function () {
    jQuery(this).parents("div.creator-card").toggleClass("expanded");
  });

  localizeDateTimes(jQuery(".creator-card"));
});
