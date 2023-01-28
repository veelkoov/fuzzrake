declare const DATA_BRIDGE: { [key: string]: string };

export type SpecialItem = { 'value': string, 'label': string, 'count': number, 'faIcon': string };
export type SpecialItems = Array<SpecialItem>;
export type StringItem = { 'value': string, 'label': string, 'count': number };
export type StringItems = Array<StringItem>;
export type StringItemsItem = { 'value': StringItems, 'label': string, 'count': number };
export type SpecieItem = { 'value': string|SpecieItems, 'label': string, 'count': number };
export type SpecieItems = Array<SpecieItem>;

export type MultiselectOptions = { 'items': StringItems, 'specialItems': SpecialItems };
export type CountriesOptions = { 'items': StringItemsItem, 'specialItems': SpecialItems };
export type SpeciesOptions = { 'items': SpecieItems, 'specialItems': SpecialItems };
export type AnyOptions = MultiselectOptions|CountriesOptions|SpeciesOptions;

export type FiltersOptions = {
    'orderTypes': MultiselectOptions,
    'styles': MultiselectOptions,
    'paymentPlans': MultiselectOptions,
    'features': MultiselectOptions,
    'productionModels': MultiselectOptions,
    'openFor': MultiselectOptions,
    'languages': MultiselectOptions,
    'countries': CountriesOptions,
    'states': MultiselectOptions,
    'species': SpeciesOptions,
};

declare const FILTERS_OPTIONS: FiltersOptions;
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

    public static getFiltersOptions(): FiltersOptions {
        return FILTERS_OPTIONS;
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
