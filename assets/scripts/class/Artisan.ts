import {ADULTS, MINORS, MIXED} from '../consts';

export default class Artisan {
    public static readonly DATA_COMPLETE_LEVEL_PERFECT = 100;
    public static readonly DATA_COMPLETE_LEVEL_GREAT = 80;
    public static readonly DATA_COMPLETE_LEVEL_GOOD = 65;
    public static readonly DATA_COMPLETE_LEVEL_OK = 50;

    public static readonly NEW_ARTISANS_MAX_AGE_SECONDS = 42 * 24 * 60 * 60; // grep-amount-of-days-considered-new

    readonly location: string;
    readonly lcCountry: string;
    readonly abSearchJson: string;
    readonly completenessGood: boolean;

    readonly isStatusKnown: boolean;
    readonly isNew: boolean;
    readonly isTracked: boolean;
    readonly cstIssueText: string;
    readonly gotSpeciesInfo: boolean;
    readonly searchableText: string;

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
                readonly mastodonUrl: string,
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
        this.completenessGood = completeness > Artisan.DATA_COMPLETE_LEVEL_GOOD;
        this.isStatusKnown = this.openFor.length + this.closedFor.length > 0;
        this.abSearchJson = this.getAbSearchJson();
        this.isNew = this.getIsNew(dateAdded);

        this.isTracked = 0 !== commissionsUrls.length;
        this.cstIssueText = this.getCstIssueText();

        this.gotSpeciesInfo = 0 !== speciesDoes.length || 0 !== speciesDoesnt.length;

        this.searchableText = `${name}\n${formerly}\n${makerId}\n${formerMakerIds}`.toLowerCase();
    }

    private getCstIssueText(): string {
        if (!this.isTracked || !this.csTrackerIssue) {
            return '';
        }

        return  0 !== this.openFor.length || 0 !== this.closedFor.length ? 'Unsure' : 'Unknown';
    }

    public getAges(): string {
        switch (this.ages) {
            case MINORS:
                return MINORS;
            case MIXED:
                return MIXED;
            case ADULTS:
                return ADULTS;
            default:
                return true === this.isMinor ? MINORS : '';
        }
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

    private getIsNew(dateAdded: string): boolean {
        if ('unknown' === dateAdded) {
            return false;
        }

        dateAdded = dateAdded.replace(' ', 'T') + '.000Z'; // Y-m-d H:i:s ---> YYYY-MM-DDTHH:mm:ss.sssZ

        const dateAddedParsedMs = Date.parse(dateAdded);
        const cutoffDateMs = Date.now() - (Artisan.NEW_ARTISANS_MAX_AGE_SECONDS * 1000);

        return cutoffDateMs < dateAddedParsedMs;
    }

    private getAbSearchJson(): string {
        let names = [this.name];
        names.push(...this.formerly);

        return JSON.stringify(names)
    }

    public static empty(): Artisan {
        return new Artisan('', [], '', [], '', '', '', '', [], '', '', '', '', [], '', [], [], '', [], [], '', [], [], [], [], [], '', [], [], null, '', null, null, null, null, null, '', '', [], [], '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', [], [], '', '', '', '', null, [], [], 0, '', '');
    }

    public static fromArray(data: string[]|string|number|boolean|null): Artisan {
        return new Artisan(data[0], data[1], data[2], data[3], data[4], data[5], data[6], data[7], data[8], data[9], data[10], data[11], data[12], data[13], data[14], data[15], data[16], data[17], data[18], data[19], data[20], data[21], data[22], data[23], data[24], data[25], data[26], data[27], data[28], data[29], data[30], data[31], data[32], data[33], data[34], data[35], data[36], data[37], data[38], data[39], data[40], data[41], data[42], data[43], data[44], data[45], data[46], data[47], data[48], data[49], data[50], data[51], data[52], data[53], data[54], data[55], data[56], data[57], data[58], data[59], data[60], data[61], data[62], data[63], data[64], data[65], data[66], data[67], data[68]);
    }
}
