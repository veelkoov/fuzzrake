import MessageBus from './MessageBus';

export default class FiltersManager {
    public constructor(
        private readonly messageBus: MessageBus,
    ) {
        this.setupSpeciesFiltersToggleButtons();
    }

    private initFilters(): void { // TODO: https://github.com/veelkoov/fuzzrake/issues/175
        // for (let filter of this.filters) {
        //     filter.restoreChoices();
        // }
    }

    private setupSpeciesFiltersToggleButtons(): void {
        jQuery('#filtersModal .specie .toggle').on('click', function () {
            jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
        });
    }

    public triggerUpdate(): void {
        // for (let filter of this.filters) {
        //     filter.saveChoices();
        // }

        this.messageBus.requestDataLoad(this.getQuery(), false);
    }

    private getQuery(): string {
        return ''; // FIXME
    }
}
