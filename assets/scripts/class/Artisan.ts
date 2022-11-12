export default class Artisan {
    public static readonly DATA_COMPLETE_LEVEL_PERFECT = 100;
    public static readonly DATA_COMPLETE_LEVEL_GREAT = 80;
    public static readonly DATA_COMPLETE_LEVEL_GOOD = 65;
    public static readonly DATA_COMPLETE_LEVEL_OK = 50;

    public static readonly NEW_ARTISANS_MAX_AGE_SECONDS = 42 * 24 * 60 * 60; // grep-amount-of-days-considered-new

    readonly location: string;
    readonly lcCountry: string;
    readonly allStyles: string[];
    readonly allOrderTypes: string[];
    readonly allFeatures: string[];
    readonly abSearchJson: string;
    readonly completenessGood: boolean;

    readonly isStatusKnown: boolean;
    readonly isNew: boolean;
    readonly isTracked: boolean;
    readonly cstIssueText: string;
    readonly gotSpeciesInfo: boolean;

    constructor(readonly makerId: string,
                readonly formerMakerIds: string[],

                readonly name: string,
                readonly formerly: string[],

                readonly dateAdded: string,
                readonly dateUpdated: string,

                readonly intro: string,
                readonly since: string,

                readonly languages: string[],
                readonly country: string,
                readonly state: string,
                readonly city: string,

                readonly productionModelsComment: string,
                readonly productionModels: string[],

                readonly stylesComment: string,
                readonly styles: string[],
                readonly otherStyles: string[],

                readonly orderTypesComment: string,
                readonly orderTypes: string[],
                readonly otherOrderTypes: string[],

                readonly featuresComment: string,
                readonly features: string[],
                readonly otherFeatures: string[],

                readonly paymentPlans: string[],
                readonly paymentMethods: string[],
                readonly currenciesAccepted: string[],

                readonly speciesComment: string,
                readonly speciesDoes: string[],
                readonly speciesDoesnt: string[],

                readonly isMinor: boolean,
                readonly ages: string,
                readonly nsfwWebsite: boolean,
                readonly nsfwSocial: boolean,
                readonly doesNsfw: boolean,
                readonly safeDoesNsfw: boolean,
                readonly safeWorksWithMinors: boolean,

                readonly fursuitReviewUrl: string,
                readonly websiteUrl: string,
                readonly pricesUrls: string[],
                readonly commissionsUrls: string[],
                readonly faqUrl: string,
                readonly furAffinityUrl: string,
                readonly deviantArtUrl: string,
                readonly twitterUrl: string,
                readonly facebookUrl: string,
                readonly tumblrUrl: string,
                readonly instagramUrl: string,
                readonly youtubeUrl: string,
                readonly linklistUrl: string,
                readonly furryAminoUrl: string,
                readonly etsyUrl: string,
                readonly theDealersDenUrl: string,
                readonly otherShopUrl: string,
                readonly queueUrl: string,
                readonly scritchUrl: string,
                readonly furtrackUrl: string,
                readonly photoUrls: string[],
                readonly miniatureUrls: string[],
                readonly otherUrls: string,

                readonly notes: string,
                readonly inactiveReason: string,
                readonly csLastCheck: string,
                readonly csTrackerIssue: boolean,
                readonly openFor: string[],
                readonly closedFor: string[],
                readonly completeness: number,

                readonly contactAllowed: string,
                readonly contactInfoObfuscated: string,
    ) {
        this.location = [state, city].filter(i => i).join(', ');
        this.lcCountry = country.toLowerCase();
        this.allFeatures = Artisan.makeAllList(features, otherFeatures);
        this.allStyles = Artisan.makeAllList(styles, otherStyles);
        this.allOrderTypes = Artisan.makeAllList(orderTypes, otherOrderTypes);
        this.completenessGood = completeness > Artisan.DATA_COMPLETE_LEVEL_GOOD;
        this.isStatusKnown = this.openFor.length + this.closedFor.length > 0;
        this.abSearchJson = this.getAbSearchJson();

        // TODO: Send null instead of "unknown" for dates
        // TODO: Date.parse() is not recommended?
        const dateAddedParsedMs = Date.parse(dateAdded);
        const cutoffDateMs = Date.now() - (Artisan.NEW_ARTISANS_MAX_AGE_SECONDS * 1000);
        this.isNew = dateAdded !== 'unknown' && cutoffDateMs < dateAddedParsedMs;

        this.isTracked = 0 !== openFor.length || 0 !== closedFor.length || csTrackerIssue;
        this.cstIssueText = !this.isTracked || !csTrackerIssue ? '' : (
            0 !== openFor.length || 0 !== closedFor.length ? 'Unsure' : 'Unknown'
        );

        this.gotSpeciesInfo = 0 !== speciesDoes.length || 0 !== speciesDoesnt.length;
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

    private getAbSearchJson(): string {
        let names = [this.name];
        names.push(...this.formerly);

        return JSON.stringify(names)
    }

    private static makeAllList(list: string[], other: string[]): string[] {
        let result = list.slice();

        if (other.length) {
            result.push(other.join('; '));
        }

        return result;
    }
}
