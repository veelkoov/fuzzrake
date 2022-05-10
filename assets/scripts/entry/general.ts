import jQuery = require('jquery');
import moment = require('moment');

require('../../styles/general.less');

import 'bootstrap/dist/js/bootstrap.bundle.min'
import 'bootstrap/dist/css/bootstrap.min.css'
import '@fortawesome/fontawesome-free/css/all.min.css'

jQuery(() => {
    jQuery('span.utc_datetime').each((index, element) => {
        let $span = jQuery(element);

        let parts = $span.text().match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}) UTC$/); // grep-expected-utc-datetime-format

        if (null === parts) {
            return;
        }

        $span.attr('title', $span.text());

        let originalIsoTime = `${parts[1]}T${parts[2]}:00Z`;

        $span.html(moment(originalIsoTime).local().format('YYYY-MM-DD HH:mm'));
    });
});
