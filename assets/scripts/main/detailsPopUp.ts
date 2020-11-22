import * as Handlebars from "handlebars";
import * as Utils from "./utils";
import Artisan from "../class/Artisan";
import HandlebarsHelpers from "../class/HandlebarsHelpers";
import Tracking from "../class/Tracking";

let template: HandlebarsTemplateDelegate;
let $template: JQuery<HTMLElement>;
let $contents: JQuery<HTMLElement>;

function populatePopUpWithData(artisan: Artisan): void {
    $contents.html(template({
        'artisan': artisan,
    }));

    Utils.updateUpdateRequestData('updateRequestFull', artisan);

    Tracking.setupOnLinks('#artisanLinks a', 'artisan-modal');
}

function detailsPopUpShowCallback(event: any) {
    populatePopUpWithData(jQuery(event.relatedTarget).closest('tr').data('artisan'));
}

export function init(): (() => void)[] {
    return [
        () => {
            $template = jQuery('#artisanDetailsTemplate');
            $contents = jQuery('#artisanDetailsModalContent');

            jQuery('#artisanDetailsModal').on('show.bs.modal', detailsPopUpShowCallback);
        },
        () => {
            Handlebars.registerHelper(HandlebarsHelpers.getHelpersToRegister());
        },
        () => {
            $template.find('a[data-href]').each((index: number, element: HTMLElement) => {
                /* Grep code for WORKAROUND_PLACEHOLDERS_CREATING_FAKE_404S: data-href ---> href */
                element.setAttribute('href', element.getAttribute('data-href') || '');
                element.removeAttribute('data-href');
            });
        },
        () => {
            template = Handlebars.compile($template.html(), {
                assumeObjects: true,
                data: false,
                knownHelpersOnly: true,
                knownHelpers: HandlebarsHelpers.getKnownHelpersObject(),
            });
        },
    ];
}
