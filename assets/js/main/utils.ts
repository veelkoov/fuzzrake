'use strict';

import * as $ from "jquery";
import Artisan from "./Artisan";

declare const IU_FORM_REDIRECT_URL: string;
declare const REQUEST_FORM_URL: string;

export function updateUpdateRequestData(divId: string, artisan: Artisan): void {
    $(`#${divId} .twitterUrl`).attr('href', getTwitterGuestRequestUrl(artisan));
    $(`#${divId} .artisanGoogleFormUrl`).attr('href', getMakerUpdatePrefilledUrl(artisan));
    $(`#${divId} .guestGoogleFormUrl`).attr('href', getGuestRequestPrefilledUrl(artisan));
}

function getTwitterGuestRequestUrl(artisan: Artisan) {
    return 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent(artisan.name) + '%20(please%20describe%20details)&tw_p=tweetbutton';
}

function getMakerUpdatePrefilledUrl(artisan: Artisan): string {
    return IU_FORM_REDIRECT_URL.replace('MAKER_ID', artisan.getLastMakerId())
}

function getGuestRequestPrefilledUrl(artisan: Artisan): string {
    return REQUEST_FORM_URL + '?usp=pp_url&entry.1289735951=' + encodeURIComponent(artisan.name);
}