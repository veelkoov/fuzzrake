import "../../styles/general.scss";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import Navbar from "../class/navbar/Navbar";
import { localizeDateTimes } from "../datetimes";

// @ts-expect-error I am incompetent, and I don't care to learn frontend
global.jQuery = jQuery;

jQuery(() => {
  localizeDateTimes(jQuery("body"));
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

jQuery('#top-menu-container')
  .on('navbar-init', () => Navbar.init())
  .trigger('navbar-init');
