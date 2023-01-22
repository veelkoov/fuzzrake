import FilterState from './FilterState';
import {CountriesFilterData, MultiselectFilterData, SpeciesFilterData} from '../Static';

export default class FilterDef {
    public readonly state: FilterState = new FilterState();

    constructor(
        public readonly groupName: string,
        public readonly label: string,
        public readonly filterComponentName: string,
        public readonly helpComponentName: string,
        public readonly data: CountriesFilterData|SpeciesFilterData|MultiselectFilterData,
    ) {
    }
}
