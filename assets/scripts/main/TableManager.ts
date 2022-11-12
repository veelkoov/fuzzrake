import DataBridge from '../data/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {artisanFromArray} from './utils';

export default class TableManager {
    private readonly template: HandlebarsTemplateDelegate;

    public constructor(
        private readonly $body: JQuery,
    ) {
        this.template = require('../../templates/artisan_row.handlebars');
    }

    public updateWith(data): void { // TODO: Typehint
        this.$body.empty();

        const $rows = jQuery('<div></div>');

        data.forEach((item, index) => {
            const contents = this.template({
                'index': index,
                'artisan': artisanFromArray(item),
                'trackingUrl': DataBridge.getTrackingUrl(),
                'trackingFailedImgSrc': DataBridge.getTrackingFailedImgSrc(),
            }, HandlebarsHelpers.tplCfg());

            $rows.append(contents);
        });

        this.$body.append($rows.children());
    }
}
