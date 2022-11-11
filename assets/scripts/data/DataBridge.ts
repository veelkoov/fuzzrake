declare const DATA_BRIDGE: { [key: string]: string };

declare const MAKER_IDS_MAP: object;

export default abstract class DataBridge {
    public static getTrackingUrl(): string {
        return DATA_BRIDGE.trackingUrl;
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

    public static getMakerIdsMap(): object {
        return MAKER_IDS_MAP;
    }
}
