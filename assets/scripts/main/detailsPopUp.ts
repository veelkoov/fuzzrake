import DataBridge from '../class/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {getArtisanFromRelated} from './utils';

const template = require('../../templates/artisan.handlebars');
let $contents: JQuery<HTMLElement>;

function detailsPopUpShowCallback(event: any): void {
    let artisan = getArtisanFromRelated(event);

    $contents.html(template({
        'artisan': artisan,
        'trackingUrl': DataBridge.getTrackingUrl(),
        'trackingFailedImgSrc': DataBridge.getTrackingFailedImgSrc(),
    }, HandlebarsHelpers.tplCfg()));

    $contents.data('artisan', artisan);
}

export function init(): (() => void)[] {
    return [
        () => {
            $contents = jQuery('#artisanDetailsModalContent');

            jQuery('#artisanDetailsModal').on('show.bs.modal', detailsPopUpShowCallback);
        },
    ];
}
