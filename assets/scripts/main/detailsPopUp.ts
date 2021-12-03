import * as Handlebars from "handlebars/runtime";
import * as Utils from "./utils";
import HandlebarsHelpers from "../class/HandlebarsHelpers";
import Tracking from "../class/Tracking";
import DataBridge from "../class/DataBridge";

const template = require('../templates/artisan.handlebars');
let $contents: JQuery<HTMLElement>;

function detailsPopUpShowCallback(event: any): void {
    $contents.html(template({
        'artisan': jQuery(event.relatedTarget).closest('tr').data('artisan'),
        'trackingUrl': DataBridge.getTrackingUrl(),
        'trackingFailedImgSrc': DataBridge.getTrackingFailedImgSrc(),
    }, {
        assumeObjects: true,
        data: false,
        knownHelpersOnly: true,
        knownHelpers: HandlebarsHelpers.getKnownHelpersObject(),
    }));

    Utils.updateUpdateRequestData('updateRequestFull', jQuery(event.relatedTarget).closest('tr').data('artisan'));

    Tracking.setupOnLinks('#artisanLinks a', 'artisan-modal');
}

export function init(): (() => void)[] {
    return [
        () => {
            $contents = jQuery('#artisanDetailsModalContent');

            jQuery('#artisanDetailsModal').on('show.bs.modal', detailsPopUpShowCallback);
        },
        () => {
            Handlebars.registerHelper(HandlebarsHelpers.getHelpersToRegister());
        },
    ];
}
