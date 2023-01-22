import AllSetUnOtFilter from '../filters/data/AllSetUnOtFilter';
import AnySetUnFilter from '../filters/data/AnySetUnFilter';
import AnySetUnOtFilter from '../filters/data/AnySetUnOtFilter';
import FilterVisInterface from '../filters/ui/FilterVisInterface';
import GenericFilterVis from '../filters/ui/GenericFilterVis';
import MessageBus from './MessageBus';
import OpenForFilter from '../filters/data/OpenForFilter';
import Species from '../species/Species';
import SpeciesFilterVis from '../filters/ui/SpeciesFilterVis';
import ValueUnFilter from '../filters/data/ValueUnFilter';

export default class FiltersManager {
    private filters: FilterVisInterface[] = [];

    public constructor(
        private readonly messageBus: MessageBus,
        private readonly $filters: JQuery,
    ) {
        this.initFilters();
        this.setupSpeciesFiltersToggleButtons();
    }

    private initFilters(): void { // TODO: https://github.com/veelkoov/fuzzrake/issues/175
        // this.filters.push(new GenericFilterVis<string>('countries', new ValueUnFilter('country')));
        // this.filters.push(new GenericFilterVis<string>('states', new ValueUnFilter('state')));
        // this.filters.push(new GenericFilterVis<string>('paymentPlans', new ValueUnFilter('filterPayPlans')));
        // this.filters.push(new GenericFilterVis<string>('styles', new AnySetUnOtFilter('styles')));
        // this.filters.push(new GenericFilterVis<string>('features', new AllSetUnOtFilter('features')));
        // this.filters.push(new GenericFilterVis<string>('orderTypes', new AnySetUnOtFilter('orderTypes')));
        // this.filters.push(new GenericFilterVis<string>('productionModels', new AnySetUnFilter('productionModels')));
        // this.filters.push(new GenericFilterVis<string>('languages', new AnySetUnFilter('languages')));
        // this.filters.push(new GenericFilterVis<boolean>('commissionStatuses', new OpenForFilter('openFor')));
        // this.filters.push(new SpeciesFilterVis('species', 'speciesDoes', Species.get()));
        //
        // for (let filter of this.filters) {
        //     filter.restoreChoices();
        // }
    }

    private setupSpeciesFiltersToggleButtons(): void {
        jQuery('#filtersModal .specie .toggle').on('click', function () {
            jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
        });
    }

    public getTriggerUpdateCallback(): () => void {
        return () => this.triggerUpdate();
    }

    public triggerUpdate(): void {
        for (let filter of this.filters) {
            filter.saveChoices();
        }

        this.messageBus.notifyActiveFiltersCountUpdate(this.getActiveCount());
        this.messageBus.requestDataLoad(this.getQuery(), false)
    }

    private getQuery(): string {
        return this.$filters.serialize();
    }

    private getActiveCount(): number {
        let count: number = 0;

        for (let filterId in this.filters) {
            if (this.filters[filterId].isActive()) {
                count++;
            }
        }

        return count;
    }
}
