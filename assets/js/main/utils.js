'use strict';

import $ from "jquery";

export function makeLinksOpenNewTab(linkSelector) {
    $(linkSelector).click(function (evt) {
        evt.preventDefault();
        window.open(this.href);
    });
}

export function updateUpdateRequestData(divId, $row) {
    $(`#${divId} .twitterUrl`).attr('href', 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent($row.data('name')) + '%20(please%20describe%20details)&tw_p=tweetbutton');

    $(`#${divId} .googleFromUrl`).attr('href', 'https://docs.google.com/forms/d/e/1FAIpQLSd72ex2FgHbJvkPRiADON0oCJx75JzQQCOLEQIGaSt3DSy2-Q/viewform?usp=pp_url&entry.1289735951=' + encodeURIComponent($row.data('name')));
}

export function countryFlagHtml(country) {
    return country === '' ? '' : `&nbsp;<span class="flag-icon flag-icon-${country.toLowerCase()}"></span>`;
}

export function getLinksArray(artisan) {
    let links = [];

    if (artisan.fursuitReviewUrl) {
        links.push(`<a href="${artisan.fursuitReviewUrl}"><i class="fas fa-balance-scale"></i> FursuitReview</a>`);
    }
    if (artisan.websiteUrl) {
        links.push(`<a href="${artisan.websiteUrl}"><i class="fas fa-link"></i> Official website</a>`);
    }
    if (artisan.furAffinityUrl) {
        links.push(`<a href="${artisan.furAffinityUrl}"><img src="FurAffinity.svg" alt=""/> FurAffinity</a>`);
    }
    if (artisan.deviantArtUrl) {
        links.push(`<a href="${artisan.deviantArtUrl}"><i class="fab fa-deviantart"></i> DeviantArt</a>`);
    }
    if (artisan.twitterUrl) {
        links.push(`<a href="${artisan.twitterUrl}"><i class="fab fa-twitter"></i> Twitter</a>`);
    }
    if (artisan.facebookUrl) {
        links.push(`<a href="${artisan.facebookUrl}"><i class="fab fa-facebook"></i> Facebook</a>`);
    }
    if (artisan.tumblrUrl) {
        links.push(`<a href="${artisan.tumblrUrl}"><i class="fab fa-tumblr"></i> Tumblr</a>`);
    }
    if (artisan.instagramUrl) {
        links.push(`<a href="${artisan.instagramUrl}"><i class="fab fa-instagram"></i> Instagram</a>`);
    }

    return links;
}
