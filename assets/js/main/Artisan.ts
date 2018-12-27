import * as Consts from './consts';

export default class Artisan {
    constructor(readonly name: string,
                readonly completeness: number,
                readonly formerly: string,
                readonly intro: string,
                readonly since: string,
                readonly country: string,
                readonly state: string,
                readonly city: string,
                readonly styles: string[],
                readonly otherStyles: string[],
                readonly types: string[],
                readonly otherTypes: string[],
                readonly features: string[],
                readonly otherFeatures: string[],
                readonly fursuitReviewUrl: string,
                readonly websiteUrl: string,
                readonly furAffinityUrl: string,
                readonly deviantArtUrl: string,
                readonly twitterUrl: string,
                readonly facebookUrl: string,
                readonly tumblrUrl: string,
                readonly instagramUrl: string,
                readonly youtubeUrl: string,
                readonly commisionsQuotesCheckUrl: string,
                readonly queueUrl: string,
                readonly pricesUrl: string,
                readonly faqUrl: string,
                readonly otherUrls: string,
                readonly notes: string,
                readonly paymentPlans: string,
                readonly productionModel: string[],
                readonly speciesDoes: string,
                readonly speciesDoesnt: string,
                readonly languages: string,
                readonly makerId: string,
                readonly areCommissionsOpen?: boolean,
                readonly commissionsQuotesLastCheck?: string) {
    }

    static fromArray(cells: string[]): Artisan {
        return new Artisan(
            cells[Consts.NAME_COL_IDX],
            parseInt(cells[Consts.COMPLETENESS_COL_IDX]),
            cells[Consts.FORMERLY_COL_IDX],
            cells[Consts.INTRO_COL_IDX],
            cells[Consts.SINCE_COL_IDX],
            cells[Consts.COUNTRY_COL_IDX],
            cells[Consts.STATE_COL_IDX],
            cells[Consts.CITY_COL_IDX],
            Artisan.toArray(cells[Consts.STYLES_COL_IDX], '\n'),
            Artisan.toArray(cells[Consts.OTHER_STYLES_COL_IDX], '\n'),
            Artisan.toArray(cells[Consts.TYPES_COL_IDX], '\n'),
            Artisan.toArray(cells[Consts.OTHER_TYPES_COL_IDX], '\n'),
            Artisan.toArray(cells[Consts.FEATURES_COL_IDX], '\n'),
            Artisan.toArray(cells[Consts.OTHER_FEATURES_COL_IDX], '\n'),
            cells[Consts.FURSUITREVIEW_URL_COL_IDX],
            cells[Consts.WEBSITE_URL_COL_IDX],
            cells[Consts.FURAFFINITY_URL_COL_IDX],
            cells[Consts.DEVIANTART_URL_COL_IDX],
            cells[Consts.TWITTER_URL_COL_IDX],
            cells[Consts.FACEBOOK_URL_COL_IDX],
            cells[Consts.TUMBLR_URL_COL_IDX],
            cells[Consts.INSTAGRAM_URL_COL_IDX],
            cells[Consts.YOUTUBE_URL_COL_IDX],
            cells[Consts.COMMISIONSQUOTESCHECK_URL_COL_IDX],
            cells[Consts.QUEUE_URL_COL_IDX],
            cells[Consts.PRICES_URL_COL_IDX],
            cells[Consts.FAQ_URL_COL_IDX],
            cells[Consts.OTHER_URLS_COL_IDX],
            cells[Consts.NOTES_COL_IDX],
            cells[Consts.PAYMENT_PLANS_COL_IDX],
            Artisan.toArray(cells[Consts.PRODUCTION_MODEL_COL_IDX], '\n'),
            cells[Consts.SPECIES_DOES_COL_IDX],
            cells[Consts.SPECIES_DOESNT_COL_IDX],
            cells[Consts.LANGUAGES_COL_IDX],
            cells[Consts.MAKER_ID_COL_IDX],
            Artisan.toBoolean(cells[Consts.COMMISSIONS_COL_IDX]),
            cells[Consts.COMMISSIONS_LAST_CHECK_COL_IDX]
        );
    }

    private static toArray(input: string, separator: string) {
        return input.split(separator).filter(value => value !== '');
    }

    private static toBoolean(input?: string) {
        switch (input) {
            case 'open':
                return true;
            case 'closed':
                return false;
            default:
                return null;
        }
    }
}
