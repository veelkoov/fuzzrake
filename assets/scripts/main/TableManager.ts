import ColumnsManager from './ColumnsManager';
import DataBridge from '../data/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {artisanFromArray} from './utils';
import {DataRow} from './DataManager';

export default class TableManager {
    private readonly template: HandlebarsTemplateDelegate;

    public constructor(
        private readonly columnsManager: ColumnsManager,
        private readonly $body: JQuery,
    ) {
        this.template = require('../../templates/artisan_row.handlebars');
    }

    public updateWith(data: DataRow[]): void {
        this.$body.empty();

        const columnsVisibility = this.columnsManager.getColumnsVisibility();
        const $rows = jQuery('<div></div>');

        data.forEach((item, index) => {
            const contents = this.template({
                'index': index,
                'artisan': artisanFromArray(item),
                'visibility': columnsVisibility,
            }, HandlebarsHelpers.tplCfg());

            $rows.append(contents);
        });

        this.$body.append($rows.children());
    }
}
