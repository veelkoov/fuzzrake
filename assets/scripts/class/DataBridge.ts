import Artisan from './Artisan';

declare const DATA_BRIDGE: { [key: string]: string };

declare const ARTISANS: Artisan[];
declare const MAKER_IDS_MAP: object;
declare const SPECIES: object;

export default abstract class DataBridge {
    public static getTrackingUrl(): string {
        return DATA_BRIDGE.trackingUrl;
    }

    public static getInfoUrl(): string {
        return DATA_BRIDGE.infoUrl;
    }

    public static getIuFormRedirectUrl(): string {
        return DATA_BRIDGE.iuFormRedirectUrl;
    }

    public static getFeedbackFormUrl(): string {
        return DATA_BRIDGE.feedbackFormUrl;
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

    public static getArtisans(): Artisan[] {
        return ARTISANS;
    }

    public static getMakerIdsMap(): object {
        return MAKER_IDS_MAP;
    }

    public static getSpecies(): object {
        return SPECIES;
    }
}
