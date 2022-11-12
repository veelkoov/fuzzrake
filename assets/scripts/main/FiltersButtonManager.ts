export default class FiltersButtonManager {
    public constructor(
        private $filtersButton: JQuery,
    ) {
    }

    public refreshButton(activeFiltersCount: number): void {
        let badge = activeFiltersCount > 0 ? ` <span class="badge rounded-pill bg-light text-dark">${activeFiltersCount}</span>` : '';

        this.$filtersButton.html(`Filters${badge}`);
    }
}
