import FilterState from './FilterState';
import Storage from '../../../class/Storage';
import {AnyOptions} from '../../../Static';
import {Ref, ref} from 'vue';

export default class Filter<T extends AnyOptions> {
    public readonly state: FilterState = new FilterState(this.isAndRelation);

    constructor(
        public readonly groupName: string,
        public readonly label: string,
        public readonly bodyComponentName: string,
        public readonly helpComponentName: string,
        public readonly options: T,
        public readonly isAndRelation: boolean = false,
    ) {
    }

    public restoreChoices(): void {
        let stored: string = Storage.getString(`filters/${this.groupName}/choices`);

        if (stored) {
            let values = Array.from(stored.split('\n'));

            values.forEach(value => this.state.set(value, '', true)); // TODO: Validate and restore labels as well
        }
    }

    public saveChoices(): void {
        Storage.saveString(`filters/${this.groupName}/choices`, Array.from(this.state.valuesToLabels.keys()).join('\n'));
    }
}
