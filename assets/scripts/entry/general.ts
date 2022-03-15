import moment = require('moment');
import Storage from "../class/Storage";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import {toggle} from "../jQueryUtils";

require('../../styles/general.less');

jQuery($ => {
    $('span.utc_datetime').each((index, element) => {
        let $span = $(element);

        let parts = $span.text().match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}) UTC$/); // grep-expected-utc-datetime-format

        if (null === parts) {
            return;
        }

        $span.attr('title', $span.text());

        let originalIsoTime = `${parts[1]}T${parts[2]}:00Z`;

        $span.html(moment(originalIsoTime).local().format('YYYY-MM-DD HH:mm'));
    });
});

jQuery($ => {
    let config = AgeAndSfwConfig.getInstance();

    function setMakerModeState(enabled: boolean) {

        config.setMakerMode(enabled);
        config.save();
    }

    toggle('#maker-mode-warning', config.getMakerMode());

    $('a.disable-filters-goto-main-page').on('click', () => {
        setMakerModeState(true);
    });

    $('#btn-reenable-filters').on('click', () => {
        setMakerModeState(false);
    });
});
