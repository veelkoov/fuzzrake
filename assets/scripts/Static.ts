declare const DATA_BRIDGE: { [key: string]: string };

declare const MAKER_IDS_MAP: object;
declare const VISIBLE_SPECIES: object;
declare const REGIONS: object;
declare const TOTAL_ARTISANS_COUNT: number;

export type Regions = [{ 'name': String, 'm_count': Number, 'countries': [{ 'value': String, 'label': String, 'm_count': Number }] }];

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

    public static getRegions(): Regions {
        // @ts-ignore
        return REGIONS;
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
