declare const DATA_BRIDGE: { [key: string]: string };

declare const MAKER_IDS_MAP: object;
declare const SPECIES: object;
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

    public static getTrackingFailedImgSrc(): string {
        return DATA_BRIDGE.trackingFailedImgSrc;
    }

    public static getApiUrl(path: string): string {
        return DATA_BRIDGE.apiBaseUrl + path;
    }

    public static getGoogleRecaptchaSiteKey(): string {
        return DATA_BRIDGE.googleRecaptchaSiteKey;
    }

    public static getTotalArtisansCount(): number {
        return TOTAL_ARTISANS_COUNT;
    }

    public static getMakerIdsMap(): object {
        return MAKER_IDS_MAP;
    }

    public static getSpecies(): object {
        return SPECIES;
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
