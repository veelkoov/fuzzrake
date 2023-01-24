import FilterState from './FilterState';
import {AnyOptions} from '../Static';
import {Ref, ref} from 'vue';

export default class Filter<T extends AnyOptions> {
    public readonly state: Ref<FilterState> = ref(new FilterState());

    constructor(
        public readonly groupName: string,
        public readonly label: string,
        public readonly bodyComponentName: string,
        public readonly helpComponentName: string,
        public readonly options: T,
    ) {
    }
}
