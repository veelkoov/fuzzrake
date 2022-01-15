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
    readonly abSearchJson: string;
    readonly completenessGood: boolean;
    readonly openFor: Set<string>;
    readonly filterPayPlans: string;

    private speciesDoesntFilters: Set<string>;
    private speciesDoesFilters: Set<string>;
    private otherSpeciesDoesFilters: boolean = null; // Used by filters; FIXME: Proper accessors

    readonly isStatusKnown: boolean;

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

                readonly paymentPlans: string[],
                readonly paymentMethods: string[],
                readonly currenciesAccepted: string[],

                readonly speciesComment: string,
                readonly speciesDoes: string[],
                readonly speciesDoesnt: string[],

                readonly isMinor: boolean,
                readonly ages: string,
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
                readonly bpLastCheck: string,
                readonly bpTrackerIssue: boolean,
                openFor: string[],
                readonly closedFor: string[],
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
        this.completenessGood = completeness > Artisan.DATA_COMPLETE_LEVEL_GOOD;
        this.openFor = new Set<string>(openFor);
        this.isStatusKnown = this.openFor.size + this.closedFor.length > 0;
        this.abSearchJson = this.getAbSearchJson();
        this.filterPayPlans = this.getFilterPayPlans();
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

    private getFilterPayPlans(): string {
        if (0 === this.paymentPlans.length) {
            return '';
        } else if (1 === this.paymentPlans.length && 'None' === this.paymentPlans[0]) { // grep-payment-plans-none
            return 'Not supported';  // grep-payment-plans-none-label
        } else {
            return 'Supported'; // grep-payment-plans-any-label
        }
    }
}
