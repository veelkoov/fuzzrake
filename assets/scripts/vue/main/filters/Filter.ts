import FilterState from './FilterState';
import {AnyOptions} from '../../../Static';
import {Ref, ref} from 'vue';

export default class Filter<T extends AnyOptions> {
    public readonly state: Ref<FilterState> = ref(new FilterState(this.isAndRelation));

    constructor(
        public readonly groupName: string,
        public readonly label: string,
        public readonly bodyComponentName: string,
        public readonly helpComponentName: string,
        public readonly options: T,
        public readonly isAndRelation: boolean = false,
    ) {
    }

    // public restoreChoices(): void { TODO
    //     let stored: string = localStorage[`filters/${this.filter.getStorageName()}/choices`];
    //
    //     if (stored) {
    //         let values: string[] = stored.split('\n');
    //
    //         this.$checkboxes.each((index: number, element: HTMLInputElement): void => {
    //             element.checked = values.includes(element.value);
    //         });
    //
    //         this.dataFromUiToModel();
    //         this.refreshUi();
    //     }
    // }
    //
    // public saveChoices(): void {
    //     try {
    //         localStorage[`filters/${this.filter.getStorageName()}/choices`] = this.getSelectedChoices().join('\n');
    //     } catch (e) {
    //         // Not allowed? - I don't care then
    //     }
    // }
}
