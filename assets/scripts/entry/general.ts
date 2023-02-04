import 'bootstrap';
import * as moment from 'moment';
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';

import '../../styles/general.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'bootstrap/dist/css/bootstrap.min.css';

jQuery(() => {
    jQuery('span.utc_datetime').each((index, element) => {
        const $span = jQuery(element);

        const parts = $span.text().match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}) UTC$/); // grep-expected-utc-datetime-format

        if (null === parts) {
            return;
        }

        $span.attr('title', $span.text());

        const originalIsoTime = `${parts[1]}T${parts[2]}:00Z`;

        $span.html(moment(originalIsoTime).local().format('YYYY-MM-DD HH:mm'));
    });
});

jQuery(() => {
    jQuery('a.disable-filters-goto-main-page').on('click', () => {
        const config = AgeAndSfwConfig.getInstance();

        config.setMakerMode(true);
        config.save();
    });
});
