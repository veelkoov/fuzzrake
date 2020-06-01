import * as Handlebars from "handlebars";
import * as Utils from "./utils";
import Artisan from "../class/Artisan";
import HandlebarsHelpers from "../class/HandlebarsHelpers";

let detailsPopUpTpl: HandlebarsTemplateDelegate;
let $detailsPopUp: JQuery<HTMLElement>;

function populatePopUpWithData(artisan: Artisan): void {
    $detailsPopUp.html(detailsPopUpTpl({
        'artisan': artisan,
    }));

    Utils.updateUpdateRequestData('updateRequestFull', artisan);
}

function detailsPopUpShowCallback(event: any) {
    populatePopUpWithData(jQuery(event.relatedTarget).closest('tr').data('artisan'));
}

export function init(): (() => void)[] {
    return [
        () => {
            $detailsPopUp = jQuery('#artisanDetailsModal');

            $detailsPopUp.find('a[data-href]').each((index: number, element: HTMLElement) => {
                /* Grep code for WORKAROUND_PLACEHOLDERS_CREATING_FAKE_404S: data-href ---> href */
                element.setAttribute('href', element.getAttribute('data-href') || '');
                element.removeAttribute('data-href');
            });

            $detailsPopUp.on('show.bs.modal', detailsPopUpShowCallback);
        },
        () => {
            Handlebars.registerHelper(HandlebarsHelpers.getHelpersToRegister());
        },
        () => {
            detailsPopUpTpl = Handlebars.compile($detailsPopUp.html(), {
                assumeObjects: true,
                data: false,
                knownHelpersOnly: true,
                knownHelpers: HandlebarsHelpers.getKnownHelpersObject(),
            });
        },
    ];
}
