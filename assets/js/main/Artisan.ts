'use strict';

export default class Artisan {
    constructor(readonly makerId: string,
                readonly formerMakerIds: string[],
                readonly name: string,
                readonly formerly: string[],
                readonly intro: string,
                readonly since: string,
                readonly country: string,
                readonly state: string,
                readonly city: string,
                readonly productionModels: string[],
                readonly styles: string[],
                readonly otherStyles: string[],
                readonly orderTypes: string[],
                readonly otherOrderTypes: string[],
                readonly features: string[],
                readonly otherFeatures: string[],
                readonly paymentPlans: string,
                readonly speciesDoes: string,
                readonly speciesDoesnt: string,
                readonly fursuitReviewUrl: string,
                readonly websiteUrl: string,
                readonly pricesUrl: string,
                readonly faqUrl: string,
                readonly furAffinityUrl: string,
                readonly deviantArtUrl: string,
                readonly twitterUrl: string,
                readonly facebookUrl: string,
                readonly tumblrUrl: string,
                readonly instagramUrl: string,
                readonly youtubeUrl: string,
                readonly queueUrl: string,
                readonly scritchesUrl: string,
                readonly scritchesPhotosUrls: string,
                readonly otherUrls: string,
                readonly languages: string,
                readonly notes: string,
                readonly cstUrl: string,
                readonly commissionsStatus: boolean,
                readonly cstLastCheck: string,
                readonly completeness: number,
                readonly contactAllowed: string,
                readonly contactInfoObfuscated: string,
    ) {
    }

    public getLastMakerId(): string {
        if (this.makerId !== '') {
            return this.makerId;
        }

        if (this.formerMakerIds.length !== 0) {
            return this.formerMakerIds[0];
        }

        return '';
    }
}
