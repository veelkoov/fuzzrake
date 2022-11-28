import ColumnsManager from './ColumnsManager';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import MessageBus from './MessageBus';
import {artisanFromArray} from './utils';
import {DataRow} from './DataManager';

export default class TableManager {
    private readonly template: HandlebarsTemplateDelegate;

    public constructor(
        messageBus: MessageBus,
        private readonly columnsManager: ColumnsManager,
        private readonly $body: JQuery,
    ) {
        this.template = require('../../templates/artisan_row.handlebars');

        messageBus.listenDataChanges((newData: DataRow[]) => this.updateWith(newData));
    }

    private updateWith(data: DataRow[]): void {
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
