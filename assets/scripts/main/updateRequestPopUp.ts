import DataBridge from '../class/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {getArtisanFromRelated} from './utils';

const template = require('../../templates/updates.handlebars');
let $contents: JQuery<HTMLElement>;

function updateRequestModalShowCallback(event: any): void {
    let artisan = getArtisanFromRelated(event);

    $contents.html(template({
        'artisanName': artisan.name,
        'iuFormUrl': DataBridge.getIuFormRedirectUrl().replace('MAKER_ID', artisan.getLastMakerId()),
        'feedbackFormUrl': DataBridge.getFeedbackFormUrl() + '?maker=' + encodeURIComponent(artisan.getLastMakerId()), // grep-maker-query-parameter
        'infoPath': DataBridge.getInfoUrl(),
    }, HandlebarsHelpers.tplCfg()));
}

export function init(): (() => void)[] {
    return [
        () => {
            $contents = jQuery('#artisanUpdatesModalContent');

            jQuery('#artisanUpdatesModal').on('show.bs.modal', updateRequestModalShowCallback);
        },
    ];
}
