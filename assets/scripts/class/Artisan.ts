export default class Artisan {
    public static readonly DATA_COMPLETE_LEVEL_PERFECT = 100;
    public static readonly DATA_COMPLETE_LEVEL_GREAT = 90;
    public static readonly DATA_COMPLETE_LEVEL_GOOD = 80;
    public static readonly DATA_COMPLETE_LEVEL_OK = 60;

    readonly languages: Set<string>;
    readonly location: string;
    readonly lcCountry: string;
    readonly productionModels: Set<string>;
    readonly styles: Set<string>;
    readonly allStyles: string[];
    readonly orderTypes: Set<string>;
    readonly allOrderTypes: string[];
    readonly features: Set<string>;
    readonly allFeatures: string[];
    readonly commissionsStatusKnown: boolean;
    readonly commissionsStatusText: string;
    readonly completenessComment: string;
    readonly completenessGood: boolean;

    private speciesDoesntFilters: Set<string>;
    private speciesDoesFilters: Set<string>;
    private otherSpeciesDoesFilters: boolean = null; // Used by filters; FIXME: Proper accessors

    // noinspection OverlyComplexFunctionJS,JSUnusedGlobalSymbols
    constructor(readonly makerId: string,
                readonly formerMakerIds: string[],

                readonly name: string,
                readonly formerly: string[],

                readonly intro: string,
                readonly since: string,

                languages: string[],
                readonly country: string,
                readonly state: string,
                readonly city: string,

                readonly productionModelsComment: string,
                productionModels: string[],

                readonly stylesComment: string,
                styles: string[],
                readonly otherStyles: string[],

                readonly orderTypesComment: string,
                orderTypes: string[],
                readonly otherOrderTypes: string[],

                readonly featuresComment: string,
                features: string[],
                readonly otherFeatures: string[],

                readonly paymentPlans: string,
                readonly paymentMethods: string[],
                readonly currenciesAccepted: string[],

                readonly speciesComment: string,
                readonly speciesDoes: string[],
                readonly speciesDoesnt: string[],

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
                readonly linktreeUrl: string,
                readonly furryAminoUrl: string,
                readonly etsyUrl: string,
                readonly theDealersDenUrl: string,
                readonly otherShopUrl: string,
                readonly queueUrl: string,
                readonly scritchUrl: string,
                readonly scritchPhotoUrls: string[],
                readonly scritchMiniatureUrls: string[],
                readonly otherUrls: string,

                readonly notes: string,
                readonly inactiveReason: string,
                readonly cstUrl: string,
                readonly commissionsStatus: boolean,
                readonly cstLastCheck: string,
                readonly completeness: number,

                readonly contactAllowed: string,
                readonly contactInfoObfuscated: string,
    ) {
        this.languages = new Set<string>(languages);
        this.location = [state, city].filter(i => i).join(', ');
        this.lcCountry = country.toLowerCase();
        this.productionModels = new Set<string>(productionModels);
        this.features = new Set<string>(features);
        this.allFeatures = Artisan.makeAllList(features, otherFeatures);
        this.styles = new Set<string>(styles);
        this.allStyles = Artisan.makeAllList(styles, otherStyles);
        this.orderTypes = new Set<string>(orderTypes);
        this.allOrderTypes = Artisan.makeAllList(orderTypes, otherOrderTypes);
        this.commissionsStatusKnown = commissionsStatus !== null;
        this.commissionsStatusText = Artisan.getCommissionsStatusText(commissionsStatus);
        this.completenessComment = Artisan.getCompletenessComment(completeness);
        this.completenessGood = completeness > Artisan.DATA_COMPLETE_LEVEL_GOOD;
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

    public setHasOtherSpeciesDoesFilters(): void {
        this.otherSpeciesDoesFilters = true;
    }

    public setSpeciesDoesntFilters(speciesDoesntFilters: Set<string>): void {
        this.speciesDoesntFilters = speciesDoesntFilters;
    }

    public setSpeciesDoesFilters(speciesDoesFilters: Set<string>): void {
        this.speciesDoesFilters = speciesDoesFilters;
    }

    public getSpeciesDoesntFilters(): Set<string> {
        return this.speciesDoesntFilters;
    }

    public getSpeciesDoesFilters(): Set<string> {
        return this.speciesDoesFilters;
    }

    private static makeAllList(list: string[], other: string[]): string[] {
        let result = list.slice();

        if (other.length) {
            result.push(other.join('; '));
        }

        return result;
    }

    private static getCommissionsStatusText(commissionsStatus: boolean): string {
        return commissionsStatus === null ? 'unknown' : commissionsStatus ? 'open' : 'closed';
    }

    private static getCompletenessComment(completeness: number): string {
        if (completeness >= Artisan.DATA_COMPLETE_LEVEL_PERFECT) {
            return 'Awesome! ❤️';
        } else if (completeness >= Artisan.DATA_COMPLETE_LEVEL_GREAT) {
            return 'Great!'
        } else if (completeness >= Artisan.DATA_COMPLETE_LEVEL_GOOD) {
            return 'Good job!'
        } else if (completeness >= Artisan.DATA_COMPLETE_LEVEL_OK) {
            return 'Some updates might be helpful...';
        } else {
            return 'Yikes! :( Updates needed!';
        }
    }
}
