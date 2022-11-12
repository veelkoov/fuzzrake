import FilterVisInterface from "../filters/ui/FilterVisInterface";
import GenericFilterVis from "../filters/ui/GenericFilterVis";
import ValueUnFilter from "../filters/data/ValueUnFilter";
import AnySetUnOtFilter from "../filters/data/AnySetUnOtFilter";
import AllSetUnOtFilter from "../filters/data/AllSetUnOtFilter";
import AnySetUnFilter from "../filters/data/AnySetUnFilter";
import OpenForFilter from "../filters/data/OpenForFilter";
import SpeciesFilterVis from "../filters/ui/SpeciesFilterVis";
import FiltersButtonManager from "./FiltersButtonManager";
import DataManager from "./DataManager";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";

export default class FiltersManager {
    private filters: FilterVisInterface[] = [];

    public constructor(
        private readonly filtersButtonManager: FiltersButtonManager,
        private readonly dataManager: DataManager,
        private readonly $filters: JQuery,
    ) {
        this.initFilters();

        for (let filter of this.filters) {
            filter.restoreChoices();
        }

        this.setupSpeciesFiltersToggleButtons();
    }

    private initFilters(): void {
        this.filters.push(new GenericFilterVis<string>('countries', new ValueUnFilter('country')));
        this.filters.push(new GenericFilterVis<string>('states', new ValueUnFilter('state')));
        this.filters.push(new GenericFilterVis<string>('paymentPlans', new ValueUnFilter('filterPayPlans')));
        this.filters.push(new GenericFilterVis<string>('styles', new AnySetUnOtFilter('styles')));
        this.filters.push(new GenericFilterVis<string>('features', new AllSetUnOtFilter('features')));
        this.filters.push(new GenericFilterVis<string>('orderTypes', new AnySetUnOtFilter('orderTypes')));
        this.filters.push(new GenericFilterVis<string>('productionModels', new AnySetUnFilter('productionModels')));
        this.filters.push(new GenericFilterVis<string>('languages', new AnySetUnFilter('languages')));
        this.filters.push(new GenericFilterVis<boolean>('commissionsStatus', new OpenForFilter('openFor')));
        this.filters.push(new SpeciesFilterVis('species', 'speciesDoes'));
    }

    private setupSpeciesFiltersToggleButtons(): void { // TODO: Improve
        jQuery('#filtersModal .specie .toggle').on('click', function () {
            jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
        });
    }

    public getActiveCount(): number {
        let count: number = 0;

        for (let filterId in this.filters) {
            if (this.filters[filterId].isActive()) {
                count++;
            }
        }

        return count;
    }

    public getTriggerUpdateCallback(): () => void {
        return () => this.triggerUpdate();
    }

    public triggerUpdate(): void {
        this.filtersButtonManager.refreshButton(this.getActiveCount());
        this.dataManager.updateQuery(this.getQuery());

        for (let filter of this.filters) {
            filter.saveChoices();
        }
    }

    private getQuery(): string {
        if (AgeAndSfwConfig.getInstance().getMakerMode()) {
            return '';
        }

        return this.$filters.serialize();
    }
}
