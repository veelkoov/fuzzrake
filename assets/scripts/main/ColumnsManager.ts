import {jqTarget} from './utils';

export default class ColumnsManager {
    private readonly visibility: ColumnsVisibility;

    public constructor(
        private readonly switches: JQuery, // TODO: active/inactive
    ) {
        this.visibility = new ColumnsVisibility(); // TODO: save/load
        this.refreshColumns();
    }

    public getColumnsVisibility(): ColumnsVisibility {
        return this.visibility;
    }

    public getColumnChangedCallback(): (event: JQuery.Event) => void {
        return (event: JQuery.Event) => this.columnChanged(event);
    }

    private columnChanged(event: JQuery.Event): void {
        event.preventDefault();
        const colName = jqTarget(event).closest('li').data('colName');
        this.visibility.toggle(colName);

        this.refreshColumns();
    }

    private refreshColumns(): void {
        for (let colName in this.visibility.getValues()) {
            const $cells = jQuery(`#artisans th.${colName}, #artisans td.${colName}`)

            if (this.visibility.isVisible(colName)) {
                $cells.removeClass('d-none');
            } else {
                $cells.addClass('d-none');
            }
        }
    }

    // TODO
    // private recordColumnsVisibilityCallback(): void {
    //     try {
    //         localStorage['columns/version'] = 2; // TODO: Constant
    //         localStorage['columns/state'] = colVis.join(',');
    //     } catch (e) {
    //         // Not allowed? - I don't care then
    //     }
    // }
    //
    // private restoreColumns(): void {
    //     let states: string = localStorage['columns/state'];
    //
    //     if (localStorage['columns/version'] === columnsSetVersion && states) {
    //         let idx: number = 0;
    //
    //         for (let state of states.split(',')) {
    //             $dtDataTable.columns(idx++).visible(state === 'true');
    //         }
    //     }
    // }
}

export class ColumnsVisibility {
    private values: {[key: string]: boolean} = {
        'makerId': false,
        'state': false,
        'languages': false,
        'productionModels': false,
        'styles': true,
        'types': false,
        'features': false,
        'species': false,
        'commissions': true,
        'links': true,
    };

    public isVisible(colName: string) {
        return this.values[colName];
    }

    public toggle(colName: string) {
        return this.values[colName] = !this.values[colName];
    }

    public getValues(): {[key: string]: boolean} {
        return this.values;
    }
}