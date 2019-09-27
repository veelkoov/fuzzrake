'use strict';

import Artisan from "./Artisan";

declare const IU_FORM_REDIRECT_URL: string;
declare const REQUEST_FORM_URL: string;

export default class GoogleFormsHelper {
    public static getMakerUpdatePrefilledUrl(artisan: Artisan): string {
        return IU_FORM_REDIRECT_URL.replace('MAKER_ID', artisan.getLastMakerId())
    }

    public static getGuestRequestPrefilledUrl(artisan: Artisan): string {
        return REQUEST_FORM_URL + '?usp=pp_url&entry.1289735951=' + encodeURIComponent(artisan.name);
    }
}
