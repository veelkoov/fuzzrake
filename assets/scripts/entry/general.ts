import * as jQuery from 'jquery';
// @ts-ignore ¯\_(ツ)_/¯
window.$ = window.jQuery = jQuery

import 'bootstrap';
import * as moment from 'moment';

import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../../styles/general.less';

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
