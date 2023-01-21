declare const DATA_BRIDGE: { [key: string]: string };

export type SpecialItem = { 'value': String, 'label': String, 'count': Number, 'faIcon': String };
export type SpecialItems = Array<SpecialItem>;
export type StringItem = { 'value': String, 'label': String, 'count': Number };
export type StringItemsItem = { 'value': Array<StringItem>, 'label': String, 'count': Number };

export type Countries = {
    'items': StringItemsItem,
    'specialItems': SpecialItems,
};
export type FiltersData = {
    'orderTypes': object,
    'styles': object,
    'paymentPlans': object,
    'features': object,
    'productionModels': object,
    'commissionStatuses': object,
    'languages': object,
    'countries': Countries,
    'states': object,
    'species': object,
};

declare const MAKER_IDS_MAP: object;
declare const VISIBLE_SPECIES: object;
declare const FILTERS_DATA: FiltersData;
declare const TOTAL_ARTISANS_COUNT: number;

export default abstract class Static {
    public static getMainUrl(): string {
        return DATA_BRIDGE.mainUrl;
    }

    public static getTrackingUrl(): string {
        return DATA_BRIDGE.trackingUrl;
    }

    public static getIuFormRedirectUrl(artisanId: string): string {
        return DATA_BRIDGE.iuFormRedirectUrl.replace('MAKER_ID', artisanId);
    }

    public static getFeedbackFormUrl(artisanId: string): string {
        return DATA_BRIDGE.feedbackFormUrl.replace('MAKER_ID', artisanId);
    }

    public static getArtisanEditUrl(artisanId: string): string {
        return DATA_BRIDGE.artisanEditUrl.replace('MAKER_ID', artisanId);
    }

    public static getTrackingFailedImgSrc(): string {
        return DATA_BRIDGE.trackingFailedImgSrc;
    }

    public static getApiUrl(path: string): string {
        return DATA_BRIDGE.apiBaseUrl + path;
    }

    public static getGoogleRecaptchaSiteKey(): string {
        return DATA_BRIDGE.googleRecaptchaSiteKey;
    }

    public static getEnvironment(): string {
        return DATA_BRIDGE.environment;
    }

    public static getTotalArtisansCount(): number {
        return TOTAL_ARTISANS_COUNT;
    }

    public static getMakerIdsMap(): object {
        return MAKER_IDS_MAP;
    }

    public static getVisibleSpecies(): object {
        return VISIBLE_SPECIES;
    }

    public static getFiltersData(): FiltersData {
        return FILTERS_DATA;
    }

    public static showLoadingIndicator(): void {
        // @ts-ignore
        window.fliSetLoading(true);
    }

    public static hideLoadingIndicator(): void {
        // @ts-ignore
        window.fliSetLoading(false);
    }

    public static loadFuzzrakeData(): void {
        // @ts-ignore
        window.loadFuzzrakeData();
    }
}
