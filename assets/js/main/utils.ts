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

export function countryFlagHtml(country: string): string {
    return country === '' ? '' : `&nbsp;<span class="flag-icon flag-icon-${country.toLowerCase()}"></span>`;
}

export function getLinks$(artisan: Artisan): JQuery<HTMLElement> {
    let links = [];

    pushLink(links, artisan.fursuitReviewUrl, '<i class="fas fa-balance-scale"></i> FursuitReview', true);
    pushLink(links, artisan.websiteUrl, '<i class="fas fa-link"></i> Official website', true);
    pushLink(links, artisan.pricesUrl, '<i class="fas fa-dollar-sign"></i> Prices');
    pushLink(links, artisan.faqUrl, '<i class="fas fa-comments"></i> FAQ');
    pushLink(links, artisan.furAffinityUrl, '<img src="FurAffinity.svg" alt=""/> FurAffinity');
    pushLink(links, artisan.deviantArtUrl, '<i class="fab fa-deviantart"></i> DeviantArt');
    pushLink(links, artisan.twitterUrl, '<i class="fab fa-twitter"></i> Twitter');
    pushLink(links, artisan.facebookUrl, '<i class="fab fa-facebook"></i> Facebook');
    pushLink(links, artisan.tumblrUrl, '<i class="fab fa-tumblr"></i> Tumblr');
    pushLink(links, artisan.instagramUrl, '<i class="fab fa-instagram"></i> Instagram');

    return $(links.join(''));
}

function getTwitterGuestRequestUrl(artisan: Artisan) {
    return 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent(artisan.name) + '%20(please%20describe%20details)&tw_p=tweetbutton';
}

function pushLink(targetArray: string[], url: string, text: string, isPrimary: boolean = false): void {
    if (url) {
        targetArray.push(`<a href="${url}" ${isPrimary ? 'class="primary"' : ''}>${text}</a>`);
    }
}
