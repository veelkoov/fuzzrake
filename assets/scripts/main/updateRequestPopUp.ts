import DataBridge from '../data/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {getArtisanFromRelated} from './utils';
import DataManager from './DataManager';

const template = require('../../templates/updates.handlebars');
let $contents: JQuery<HTMLElement>;
let _dataManager: DataManager;

function updateRequestModalShowCallback(event: any): void {
    let artisan = getArtisanFromRelated(event, _dataManager);

    $contents.html(template({
        'artisanName': artisan.name,
        'iuFormUrl': DataBridge.getIuFormRedirectUrl().replace('MAKER_ID', artisan.getLastMakerId()),
        'feedbackFormUrl': DataBridge.getFeedbackFormUrl() + '?maker=' + encodeURIComponent(artisan.getLastMakerId()), // grep-maker-query-parameter
    }, HandlebarsHelpers.tplCfg()));
}

export function init(dataManager: DataManager): (() => void)[] {
    _dataManager = dataManager;

    return [
        () => {
            $contents = jQuery('#artisanUpdatesModalContent');

            jQuery('#artisanUpdatesModal').on('show.bs.modal', updateRequestModalShowCallback);
        },
    ];
}
