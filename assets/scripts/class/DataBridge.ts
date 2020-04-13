import Artisan from "./Artisan";

declare const DATA_UPDATES_URL: string;
declare const IU_FORM_REDIRECT_URL: string;
declare const REQUEST_FORM_URL: string;
declare const ARTISANS: Artisan[];
declare const MAKER_IDS_MAP: object;
declare const API_BASE_URL: string;
declare const GOOGLE_RECAPTCHA_SITE_KEY: string;

export default abstract class DataBridge {
    public static getMakerIdsMap(): object {
        return MAKER_IDS_MAP;
    }

    public static getArtisans(): Artisan[] {
        return ARTISANS;
    }

    public static getDataUpdatesUrl(): string {
        return DATA_UPDATES_URL;
    }

    public static getIuFormRedirectUrl(): string {
        return IU_FORM_REDIRECT_URL;
    }

    public static getRequestFormUrl(): string {
        return REQUEST_FORM_URL;
    }

    public static getApiUrl(path: string): string {
        return API_BASE_URL + path;
    }

    public static getGoogleRecaptchaSiteKey(): string {
        return GOOGLE_RECAPTCHA_SITE_KEY;
    }
}
