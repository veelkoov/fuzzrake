import DataBridge from '../data/DataBridge';
import DataManager from './DataManager';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {getArtisanIndexForEvent} from './utils';

export default class CardPopUpManager {
    private readonly template: HandlebarsTemplateDelegate;

    public constructor(
        private readonly dataManager: DataManager,
        private readonly $contents: JQuery,
    ) {
        this.template = require('../../templates/artisan_card.handlebars');
    }

    private showPopUpCallback(event: JQuery.Event): void {
        const index = getArtisanIndexForEvent(event);
        const artisan = this.dataManager.getArtisanByIndex(index);

        this.$contents.html(this.template({
            'artisan': artisan,
            'trackingUrl': DataBridge.getTrackingUrl(),
            'trackingFailedImgSrc': DataBridge.getTrackingFailedImgSrc(),
        }, HandlebarsHelpers.tplCfg()));

        this.$contents.data('index', index);
    }

    public getShowCallback(): (event: JQuery.Event) => void {
        return (event: JQuery.Event) => this.showPopUpCallback(event);
    }
}
