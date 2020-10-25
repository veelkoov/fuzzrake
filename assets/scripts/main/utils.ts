import Artisan from "../class/Artisan";
import DataBridge from "../class/DataBridge";

export function updateUpdateRequestData(divId: string, artisan: Artisan): void {
    jQuery(`#${divId} .artisanGoogleFormUrl`).attr('href', getIuFormLinkForArtisan(artisan));
    jQuery(`#${divId} .guestGoogleFormUrl`).attr('href', getGuestRequestPrefilledUrl(artisan));
}

function getIuFormLinkForArtisan(artisan: Artisan): string {
    return DataBridge.getIuFormRedirectUrl().replace('MAKER_ID', artisan.getLastMakerId())
}

function getGuestRequestPrefilledUrl(artisan: Artisan): string {
    return DataBridge.getRequestFormUrl() + '?usp=pp_url&entry.1289735951=' + encodeURIComponent(artisan.name);
}
