import * as Handlebars from "handlebars";
import * as Utils from "./utils";
import HandlebarsHelpers from "../class/HandlebarsHelpers";
import Tracking from "../class/Tracking";

let template: HandlebarsTemplateDelegate;
let $template: JQuery<HTMLElement>;
let $contents: JQuery<HTMLElement>;

function detailsPopUpShowCallback(event: any) {
    $contents.html(template({
        'artisan': jQuery(event.relatedTarget).closest('tr').data('artisan'),
    }));

    $contents.find('a[data-href]').each((index: number, element: HTMLElement) => {
        /* Grep code for WORKAROUND_PLACEHOLDERS_CREATING_FAKE_404S: data-href ---> href */
        element.setAttribute('href', element.getAttribute('data-href') || '');
        element.removeAttribute('data-href');
    }); // TODO: Check if this weird workaround is still required when we're using the dedicated template node

    Utils.updateUpdateRequestData('updateRequestFull', jQuery(event.relatedTarget).closest('tr').data('artisan'));

    Tracking.setupOnLinks('#artisanLinks a', 'artisan-modal');
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
            template = Handlebars.compile($template.html(), {
                assumeObjects: true,
                data: false,
                knownHelpersOnly: true,
                knownHelpers: HandlebarsHelpers.getKnownHelpersObject(),
            });
        },
    ];
}
