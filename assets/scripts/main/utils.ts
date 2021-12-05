import Artisan from "../class/Artisan";
import DataBridge from "../class/DataBridge";

export function updateUpdateRequestData(divId: string, artisan: Artisan): void {
    jQuery(`#${divId} .artisanGoogleFormUrl`).attr('href', getIuFormLinkForArtisan(artisan));
    jQuery(`#${divId} .guestGoogleFormUrl`).attr('href', getGuestReportPrefilledUrl(artisan));
}

function getIuFormLinkForArtisan(artisan: Artisan): string {
    return DataBridge.getIuFormRedirectUrl().replace('MAKER_ID', artisan.getLastMakerId())
}

function getGuestReportPrefilledUrl(artisan: Artisan): string {
    return DataBridge.getReportFormUrl() + '?usp=pp_url&entry.1289735951=' + encodeURIComponent(artisan.name);
}
