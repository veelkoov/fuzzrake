'use strict';

import $ from "jquery";

export function makeLinksOpenNewTab(linkSelector) {
    $(linkSelector).click(function (evt) {
        evt.preventDefault();
        window.open(this.href);
    });
}

export function updateUpdateRequestData(divId, $row) {
    $('#' + divId + ' .twitterUrl').attr('href', 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent($row.data('name')) + '%20(please%20describe%20details)&tw_p=tweetbutton');

    $('#' + divId + ' .googleFromUrl').attr('href', 'https://docs.google.com/forms/d/e/1FAIpQLSd72ex2FgHbJvkPRiADON0oCJx75JzQQCOLEQIGaSt3DSy2-Q/viewform?usp=pp_url&entry.1289735951=' + encodeURIComponent($row.data('name')));
}

export function countryFlagHtml(country) {
    return country === '' ? '' : `&nbsp;<span class="flag-icon flag-icon-${country.toLowerCase()}"></span>`;
}
