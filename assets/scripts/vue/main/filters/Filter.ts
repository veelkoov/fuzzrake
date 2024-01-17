import FilterState from './FilterState';
import Storage from '../../../class/Storage';
import {FilterOptions, Items} from '../../../Static';

export default class Filter {
    public readonly state: FilterState = new FilterState(this.isAndRelation);

    constructor(
        public readonly groupName: string,
        public readonly label: string,
        public readonly bodyComponentName: string,
        public readonly helpComponentName: string,
        public readonly options: FilterOptions,
        public readonly isAndRelation: boolean = false,
    ) {
    }

    public saveChoices(): void {
        Storage.saveString(`filters/${this.groupName}/choices`, Array.from(this.state.valuesToLabels.keys()).join('\n'));
    }

    public restoreChoices(): void {
        const stored: string = Storage.getString(`filters/${this.groupName}/choices`, '');

        if (stored) {
            const values = Array.from(stored.split('\n'));
            const validPairs = this.getValidValueLabelPairsFromOptions(this.options);

            values.forEach(value => {
                const label = validPairs.get(value);

                if (undefined !== label) {
                    this.state.set(value, label, true);
                }
            });
        }
    }

    private getValidValueLabelPairsFromOptions(options: FilterOptions): Map<string, string> {
        const result = new Map<string, string>();

        options.specialItems.forEach(option => result.set(option.value, option.label));

        this.getValidValueLabelPairsFromItems(options.items)
            .forEach((value, key) => result.set(key, value));

        return result;
    }

    private getValidValueLabelPairsFromItems(options: Items): Map<string, string> {
        const result = new Map<string, string>();

        options.forEach(option => {
            result.set(option.value, option.label);

            this.getValidValueLabelPairsFromItems(option.subitems)
                .forEach((value, key) => result.set(key, value));
        });

        return result;
    }
}
