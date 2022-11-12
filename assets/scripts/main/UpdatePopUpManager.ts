import DataBridge from '../data/DataBridge';
import DataManager from './DataManager';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {getArtisanIndexForEvent} from './utils';

export default class UpdatePopUpManager {
    private readonly template: HandlebarsTemplateDelegate;

    public constructor(
        private readonly dataManager: DataManager,
        private readonly $contents: JQuery,
    ) {
        this.template = require('../../templates/updates.handlebars');
    }

    private showPopUpCallback(event: JQuery.Event): void {
        const index = getArtisanIndexForEvent(event);
        const artisan = this.dataManager.getArtisanByIndex(index);

        this.$contents.html(this.template({
            'artisanName': artisan.name,
            'iuFormUrl': DataBridge.getIuFormRedirectUrl().replace('MAKER_ID', artisan.getLastMakerId()),
            'feedbackFormUrl': DataBridge.getFeedbackFormUrl() + '?maker=' + encodeURIComponent(artisan.getLastMakerId()), // grep-maker-query-parameter
        }, HandlebarsHelpers.tplCfg()));
    }

    public getShowCallback(): (event) => void {
        return (event) => this.showPopUpCallback(event);
    }
}
