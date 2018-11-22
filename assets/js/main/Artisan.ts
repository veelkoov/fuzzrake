import * as Consts from './consts';

export default class Artisan {
    constructor(readonly name: string,
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
                readonly notes: string,
                readonly areCommissionsOpen?: boolean,
                readonly commissionsQuotesLastCheck?: string) {
    }

    static fromArray(cells: string[]): Artisan {
        return new Artisan(
            cells[Consts.NAME_COL_IDX],
            cells[Consts.FORMERLY_COL_IDX],
            cells[Consts.INTRO_COL_IDX],
            cells[Consts.SINCE_COL_IDX],
            cells[Consts.COUNTRY_COL_IDX],
            cells[Consts.STATE_COL_IDX],
            cells[Consts.CITY_COL_IDX],
            Artisan.toArray(cells[Consts.STYLES_COL_IDX], ', '),
            Artisan.toArray(cells[Consts.OTHER_STYLES_COL_IDX], '; '),
            Artisan.toArray(cells[Consts.TYPES_COL_IDX], ', '),
            Artisan.toArray(cells[Consts.OTHER_TYPES_COL_IDX], '; '),
            Artisan.toArray(cells[Consts.FEATURES_COL_IDX], ', '),
            Artisan.toArray(cells[Consts.OTHER_FEATURES_COL_IDX], '; '),
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
            cells[Consts.NOTES_COL_IDX],
            Artisan.toBoolean(cells[Consts.COMMISSIONS_COL_IDX]),
            cells[Consts.COMMISSIONS_LAST_CHECK_COL_IDX]
        );
    }

    private static toArray(input: string, separator: string) {
        return input.split(separator).filter(value => value !== '');
    }

    private static toBoolean(input?: string) {
        switch (input) {
            case '1':
                return true;
            case '0':
                return false;
            default:
                return null;
        }
    }
}
