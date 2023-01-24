import FilterState from './FilterState';
import {AnyOptions} from '../Static';

export default class Filter<T extends AnyOptions> {
    public readonly state: FilterState = new FilterState();

    constructor(
        public readonly groupName: string,
        public readonly label: string,
        public readonly bodyComponentName: string,
        public readonly helpComponentName: string,
        public readonly options: T,
    ) {
    }
}
