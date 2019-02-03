'use strict';

import $ from "jquery";

export function makeLinksOpenNewTab(linkSelector) {
    $(linkSelector).click(function (evt) {
        evt.preventDefault();
        window.open(this.href);
    });
}

function toDataItem(id, data) {
    if (typeof data === 'string') {
        return `entry.${id}=${encodeURIComponent(data)}`;
    } else {
        return data.map(item => {
            return `entry.${id}=${encodeURIComponent(item)}`
        }).join('&');
    }
}

function getGoogleFormPrefilledUrl(artisan) {
    let dataItems = [];

    dataItems.push(toDataItem(646315912, artisan.name));
    dataItems.push(toDataItem(6087327, artisan.formerly));
    dataItems.push(toDataItem(764970912, artisan.since + '-01'));
    dataItems.push(toDataItem(1452524703, artisan.country));
    dataItems.push(toDataItem(355015034, artisan.state));
    dataItems.push(toDataItem(944749751, artisan.city));
    dataItems.push(toDataItem(743737005, artisan.paymentPlans));
    dataItems.push(toDataItem(2034494235, artisan.pricesUrl));
    dataItems.push(toDataItem(838362497, artisan.productionModel));
    dataItems.push(toDataItem(129031545, artisan.styles));
    dataItems.push(toDataItem(1324232796, artisan.otherStyles.join('\n')));
    dataItems.push(toDataItem(1319815626, artisan.types));
    dataItems.push(toDataItem(67316802, artisan.otherTypes.join('\n')));
    dataItems.push(toDataItem(1197078153, artisan.features));
    dataItems.push(toDataItem(175794467, artisan.otherFeatures.join('\n')));
    dataItems.push(toDataItem(416913125, artisan.speciesDoes));
    dataItems.push(toDataItem(1335617718, artisan.speciesDoesnt));
    dataItems.push(toDataItem(1291118884, artisan.fursuitReviewUrl));
    dataItems.push(toDataItem(1753739667, artisan.websiteUrl));
    dataItems.push(toDataItem(110608078, artisan.faqUrl));
    dataItems.push(toDataItem(1781081038, artisan.furAffinityUrl));
    dataItems.push(toDataItem(591054015, artisan.deviantArtUrl));
    dataItems.push(toDataItem(151172280, artisan.twitterUrl));
    dataItems.push(toDataItem(1965677490, artisan.facebookUrl));
    dataItems.push(toDataItem(1209445762, artisan.tumblrUrl));
    dataItems.push(toDataItem(696741203, artisan.instagramUrl));
    dataItems.push(toDataItem(618562986, artisan.youtubeUrl));
    dataItems.push(toDataItem(1355429885, artisan.commisionsQuotesCheckUrl));
    dataItems.push(toDataItem(1507707399, artisan.otherUrls));
    dataItems.push(toDataItem(528156817, artisan.languages));
    dataItems.push(toDataItem(927668258, artisan.makerId));
    dataItems.push(toDataItem(1671817601, artisan.notes));
    dataItems.push(toDataItem(725071599, artisan.intro));
    dataItems.push('entry.1898509469=Yes, I\'m not on the list yet, or I used the update link');

    // TODO: get form link form czpcz
    return 'https://docs.google.com/forms/d/e/1FAIpQLSd4N7m7Sga67O7jzUGuvTg6ZpFcMxQ0HtsZSkCOTSgiLBRwfQ/viewform?usp=pp_url&' + dataItems.join('&');
}

export function updateUpdateRequestData(divId, artisan) {
    $(`#${divId} .twitterUrl`).attr('href', 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent(artisan.name) + '%20(please%20describe%20details)&tw_p=tweetbutton');

    $(`#${divId} .googleFormUrl`).attr('href', getGoogleFormPrefilledUrl(artisan));
}

export function countryFlagHtml(country) {
    return country === '' ? '' : `&nbsp;<span class="flag-icon flag-icon-${country.toLowerCase()}"></span>`;
}

function pushLink(targetArray, url, text, isPrimary) {
    if (url) {
        targetArray.push(`<a href="${url}"${isPrimary ? 'class="primary"' : ''}>${text}</a>`);
    }
}

export function getLinks$(artisan) {
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
