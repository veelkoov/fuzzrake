'use strict';

import * as $ from "jquery";
import GoogleFormsHelper from "./GoogleFormsHelper";
import Artisan from "./Artisan";

export function makeLinksOpenNewTab(linkSelector: string): void {
    $(linkSelector).on('click', function (evt) {
        evt.preventDefault();
        window.open(this.getAttribute('href'));
    });
}

export function updateUpdateRequestData(divId: string, artisan: Artisan): void {
    $(`#${divId} .twitterUrl`).attr('href', getTwitterGuestRequestUrl(artisan));
    $(`#${divId} .artisanGoogleFormUrl`).attr('href', GoogleFormsHelper.getMakerUpdatePrefilledUrl(artisan));
    $(`#${divId} .guestGoogleFormUrl`).attr('href', GoogleFormsHelper.getGuestRequestPrefilledUrl(artisan));
}

function getTwitterGuestRequestUrl(artisan: Artisan) {
    return 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent(artisan.name) + '%20(please%20describe%20details)&tw_p=tweetbutton';
}
