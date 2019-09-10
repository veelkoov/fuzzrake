'use strict';

import Artisan from "./Artisan";

declare const IU_FORM_URL: string;
declare const REQUEST_FORM_URL: string;

export default class GoogleFormsHelper {
    public static getMakerUpdatePrefilledUrl(artisan: Artisan): string {
        let input = {
            646315912: artisan.name,
            6087327: artisan.formerly,
            764970912: this.transformSince(artisan.since),
            1452524703: artisan.country,
            355015034: artisan.state,
            944749751: artisan.city,
            743737005: artisan.paymentPlans,
            2034494235: artisan.pricesUrl,
            838362497: artisan.productionModels,
            129031545: artisan.styles,
            1324232796: this.transformOthersList(artisan.otherStyles),
            1319815626: artisan.orderTypes,
            67316802: this.transformOthersList(artisan.otherOrderTypes),
            1197078153: artisan.features,
            175794467: this.transformOthersList(artisan.otherFeatures),
            416913125: artisan.speciesDoes,
            1335617718: artisan.speciesDoesnt,
            1291118884: artisan.fursuitReviewUrl,
            1753739667: artisan.websiteUrl,
            110608078: artisan.faqUrl,
            1781081038: artisan.furAffinityUrl,
            591054015: artisan.deviantArtUrl,
            151172280: artisan.twitterUrl,
            1965677490: artisan.facebookUrl,
            1209445762: artisan.tumblrUrl,
            696741203: artisan.instagramUrl,
            618562986: artisan.youtubeUrl,
            1737459766: artisan.queueUrl,
            1355429885: artisan.cstUrl,
            1507707399: artisan.otherUrls,
            528156817: artisan.languages,
            927668258: artisan.makerId,
            1671817601: artisan.notes,
            725071599: artisan.intro,
            1066294270: this.transformContactAllowed(artisan.contactAllowed),
            1142456974: artisan.contactAddressObfuscated, // FIXME: virtual
            1898509469: 'Yes, I\'m not on the list yet, or I used the update link',
        };

        let urlParams = [];

        for (let id in input) {
            urlParams.push(this.toDataItem(id, input[id]));
        }

        return IU_FORM_URL + '?usp=pp_url&' + urlParams.filter(value => value !== '').join('&');
    }

    public static getGuestRequestPrefilledUrl(artisan: Artisan): string {
        return REQUEST_FORM_URL + '?usp=pp_url&' + this.toDataItem('1289735951', artisan.name);
    }

    private static toDataItem(id: string, data: string | string[]): string {
        if (typeof data === 'string') {
            return data === '' ? '' : `entry.${id}=${encodeURIComponent(data)}`;
        } else {
            return data.map(item => {
                return `entry.${id}=${encodeURIComponent(item)}`
            }).join('&');
        }
    }

    private static transformContactAllowed(contactAllowed: string): string {
        switch (contactAllowed) {
            case 'FEEDBACK':
                return 'ANNOUNCEMENTS + FEEDBACK';
            case 'ANNOUNCEMENTS':
                return 'ANNOUNCEMENTS *ONLY*';
            default:
                return 'NO (I may join Telegram)';
        }
    }

    private static transformSince(since: string): string {
        return since + '-01';
    }

    private static transformOthersList(othersList: string[]): string {
        return othersList.join('\n');
    }
}
