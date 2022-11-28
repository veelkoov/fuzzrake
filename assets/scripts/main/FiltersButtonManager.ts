import MessageBus from './MessageBus';

export default class FiltersButtonManager {
    public constructor(
        messageBus: MessageBus,
        private $filtersButton: JQuery,
    ) {
        messageBus.listenQueryUpdate((newQuery: string, newActiveFiltersCount: number) => this.refreshButton(newActiveFiltersCount));
    }

    private refreshButton(activeFiltersCount: number): void {
        let badge = activeFiltersCount > 0 ? ` <span class="badge rounded-pill bg-light text-dark">${activeFiltersCount}</span>` : '';

        this.$filtersButton.html(`Filters${badge}`);
    }
}
