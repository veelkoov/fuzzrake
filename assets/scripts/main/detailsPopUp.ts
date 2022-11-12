import DataBridge from '../data/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {getArtisanFromRelated} from './utils';
import DataManager from './DataManager';

const template = require('../../templates/artisan.handlebars');
let $contents: JQuery<HTMLElement>;
let _dataManager: DataManager;


function detailsPopUpShowCallback(event: any): void {
    let artisan = getArtisanFromRelated(event, _dataManager);

    $contents.html(template({
        'artisan': artisan,
        'trackingUrl': DataBridge.getTrackingUrl(),
        'trackingFailedImgSrc': DataBridge.getTrackingFailedImgSrc(),
    }, HandlebarsHelpers.tplCfg()));

    $contents.data('artisan', artisan);
}

export function init(dataManager: DataManager): void {
    _dataManager = dataManager;

    $contents = jQuery('#artisanDetailsModalContent');

    jQuery('#artisanDetailsModal').on('show.bs.modal', detailsPopUpShowCallback);
}
