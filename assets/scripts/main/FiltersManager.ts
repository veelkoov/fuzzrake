import MessageBus from './MessageBus';

export default class FiltersManager { // TODO: https://github.com/veelkoov/fuzzrake/issues/175
    public constructor(
        private readonly messageBus: MessageBus,
    ) {
        this.setupSpeciesFiltersToggleButtons();
    }

    private setupSpeciesFiltersToggleButtons(): void { // TODO
        jQuery('#filtersModal .specie .toggle').on('click', function () {
            jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
        });
    }

    public triggerUpdate(): void {
        this.messageBus.requestDataLoad(this.getQuery(), false);
    }

    private getQuery(): string {
        return $('#filters').serialize();
    }
}
