'use strict';

import Artisan from "./Artisan";

declare const IU_FORM_URL: string;
declare const REQUEST_FORM_URL: string;

export default class GoogleFormsHelper {
    public static getMakerUpdatePrefilledUrl(artisan: Artisan): string {
        let dataItems = [];

        dataItems.push(this.toDataItem(646315912, artisan.name));
        dataItems.push(this.toDataItem(6087327, artisan.formerly));
        dataItems.push(this.toDataItem(764970912, this.transformSince(artisan.since)));
        dataItems.push(this.toDataItem(1452524703, artisan.country));
        dataItems.push(this.toDataItem(355015034, artisan.state));
        dataItems.push(this.toDataItem(944749751, artisan.city));
        dataItems.push(this.toDataItem(743737005, artisan.paymentPlans));
        dataItems.push(this.toDataItem(2034494235, artisan.pricesUrl));
        dataItems.push(this.toDataItem(838362497, artisan.productionModels));
        dataItems.push(this.toDataItem(129031545, artisan.styles));
        dataItems.push(this.toDataItem(1324232796, this.transformOthersList(artisan.otherStyles)));
        dataItems.push(this.toDataItem(1319815626, artisan.orderTypes));
        dataItems.push(this.toDataItem(67316802, this.transformOthersList(artisan.otherOrderTypes)));
        dataItems.push(this.toDataItem(1197078153, artisan.features));
        dataItems.push(this.toDataItem(175794467, this.transformOthersList(artisan.otherFeatures)));
        dataItems.push(this.toDataItem(416913125, artisan.speciesDoes));
        dataItems.push(this.toDataItem(1335617718, artisan.speciesDoesnt));
        dataItems.push(this.toDataItem(1291118884, artisan.fursuitReviewUrl));
        dataItems.push(this.toDataItem(1753739667, artisan.websiteUrl));
        dataItems.push(this.toDataItem(110608078, artisan.faqUrl));
        dataItems.push(this.toDataItem(1781081038, artisan.furAffinityUrl));
        dataItems.push(this.toDataItem(591054015, artisan.deviantArtUrl));
        dataItems.push(this.toDataItem(151172280, artisan.twitterUrl));
        dataItems.push(this.toDataItem(1965677490, artisan.facebookUrl));
        dataItems.push(this.toDataItem(1209445762, artisan.tumblrUrl));
        dataItems.push(this.toDataItem(696741203, artisan.instagramUrl));
        dataItems.push(this.toDataItem(618562986, artisan.youtubeUrl));
        dataItems.push(this.toDataItem(1737459766, artisan.queueUrl));
        dataItems.push(this.toDataItem(1355429885, artisan.cstUrl));
        dataItems.push(this.toDataItem(1507707399, artisan.otherUrls));
        dataItems.push(this.toDataItem(528156817, artisan.languages));
        dataItems.push(this.toDataItem(927668258, artisan.makerId));
        dataItems.push(this.toDataItem(1671817601, artisan.notes));
        dataItems.push(this.toDataItem(725071599, artisan.intro));
        dataItems.push(this.toDataItem(1066294270, this.transformContactAllowed(artisan.contactAllowed)));
        dataItems.push(this.toDataItem(1142456974, artisan.contactAddressObfuscated));
        dataItems.push(this.toDataItem(1898509469, 'Yes, I\'m not on the list yet, or I used the update link'));

        return IU_FORM_URL + '?usp=pp_url&' + dataItems.filter(value => value !== '').join('&');
    }

    public static getGuestRequestPrefilledUrl(artisan: Artisan): string {
        return REQUEST_FORM_URL + '?usp=pp_url&' + this.toDataItem(1289735951, artisan.name);
    }

    private static toDataItem(id: number, data: string | string[]): string {
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
