import {jqTarget} from './utils';

export default class ColumnsManager {
    private static readonly STORAGE_VERSION: string = '2';
    private readonly visibility: ColumnsVisibility;

    public constructor(
        private readonly $switches: JQuery,
    ) {
        this.visibility = new ColumnsVisibility();
        this.restoreColumnsVisibility();
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
        this.recordColumnsVisibility();
    }

    private refreshColumns(): void {
        this.$switches.each((index, element) => {
            const $element = jQuery(element);

            const colName = $element.data('colName');

            if (this.visibility.isVisible(colName)) {
                $element.children().addClass('active');
            } else {
                $element.children().removeClass('active');
            }
        });

        for (let colName in this.visibility.getValues()) {
            const $cells = jQuery(`#artisans th.${colName}, #artisans td.${colName}`)

            if (this.visibility.isVisible(colName)) {
                $cells.removeClass('d-none');
            } else {
                $cells.addClass('d-none');
            }
        }
    }

    private recordColumnsVisibility(): void {
        try {
            localStorage['columns/version'] = ColumnsManager.STORAGE_VERSION;
            localStorage['columns/state'] = this.visibility.toString();
        } catch (e) {
            // Not allowed? - I don't care then
        }
    }

    private restoreColumnsVisibility(): void {
        let states: string = localStorage['columns/state'];

        if (localStorage['columns/version'] === ColumnsManager.STORAGE_VERSION && states) {
            for (let state of states.split(',')) {
                let parts = state.split('=');

                this.visibility.setVisible(parts[0], parts[1] === 'true');
            }
        }
    }
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

    public isVisible(colName: string): boolean {
        return this.values[colName];
    }

    public toggle(colName: string): void {
        this.values[colName] = !this.values[colName];
    }

    public setVisible(colName: string, isVisible: boolean): void {
        this.values[colName] = isVisible;
    }

    public getValues(): {[key: string]: boolean} {
        return this.values;
    }

    public toString(): string {
        let result = [];

        for (let key in this.values) {
            result.push(`${key}=${this.values[key]}`);
        }

        return result.join(',');
    }
}
