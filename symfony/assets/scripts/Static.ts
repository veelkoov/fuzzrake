declare const DATA_BRIDGE: { [key: string]: string };

export type SpecialItem = { 'value': string, 'label': string, 'count': number, 'faIcon': string };
export type SpecialItems = Array<SpecialItem>;
export type Item = { 'value': string, 'label': string, 'count': number, 'subitems': Items };
export type Items = Array<Item>;

export type FilterOptions = { 'items': Items, 'specialItems': SpecialItems };

export type FiltersOptions = {
    'orderTypes': FilterOptions,
    'styles': FilterOptions,
    'paymentPlans': FilterOptions,
    'features': FilterOptions,
    'productionModels': FilterOptions,
    'openFor': FilterOptions,
    'languages': FilterOptions,
    'countries': FilterOptions,
    'states': FilterOptions,
    'species': FilterOptions,
    'inactive': FilterOptions,
};

export default abstract class Static {
    public static getMainPath(): string {
        return DATA_BRIDGE.mainPath;
    }

    public static getTrackingPath(): string {
        return DATA_BRIDGE.trackingPath;
    }

    public static getTrackingLimitationsPath(): string {
        return DATA_BRIDGE.trackingLimitationsPath;
    }

    public static getIuFormRedirectUrl(artisanId: string): string {
        return DATA_BRIDGE.iuFormRedirectUrl.replace('MAKER_ID', artisanId);
    }

    public static getFeedbackFormPath(artisanId: string): string {
        return DATA_BRIDGE.feedbackFormPath.replace('MAKER_ID', artisanId);
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
}
