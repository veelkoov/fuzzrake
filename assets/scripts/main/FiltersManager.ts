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

    public getTriggerUpdateCallback(): () => void {
        return () => this.triggerUpdate();
    }

    public triggerUpdate(): void {
        // for (let filter of this.filters) {
        //     filter.saveChoices();
        // }

        this.messageBus.notifyActiveFiltersCountUpdate(this.getActiveCount());
        this.messageBus.requestDataLoad(this.getQuery(), false)
    }

    private getQuery(): string {
        return ''; // FIXME
    }

    private getActiveCount(): number {
        let count: number = 0;

        // for (let filterId in this.filters) { FIXME
        //     if (this.filters[filterId].isActive()) {
        //         count++;
        //     }
        // }

        return count;
    }
}
