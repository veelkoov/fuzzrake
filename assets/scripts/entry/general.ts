import * as jQuery from 'jquery';
// @ts-ignore ¯\_(ツ)_/¯
window.$ = window.jQuery = jQuery

import 'bootstrap';
import * as moment from 'moment';
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import {toggle} from '../jQueryUtils';
import * as tocbot from 'tocbot';

import '../../styles/general.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'bootstrap/dist/css/bootstrap.min.css';

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

jQuery(() => {
    tocbot.init({
        tocSelector: '#sk-toc',
        contentSelector: '#sk-content',
        headingSelector: 'h1, h2, h3, h4, h5, h6',
        extraLinkClasses: 'text-decoration-none',
        orderedList: false,
    });
});

jQuery(() => {
    let config = AgeAndSfwConfig.getInstance();

    function setMakerModeState(enabled: boolean) {
        config.setMakerMode(enabled);
        config.save();
    }

    toggle('#maker-mode-warning', config.getMakerMode());

    jQuery('a.disable-filters-goto-main-page').on('click', () => {
        setMakerModeState(true);
    });

    jQuery('#btn-reenable-filters').on('click', () => {
        setMakerModeState(false);
    });
});
