import {requireJQ, toggle} from '../jQueryUtils';

type Filter = {
    removeButton: JQuery<HTMLElement>,
    statusSpan: JQuery<HTMLElement>,
    checkboxes: JQuery<HTMLInputElement>,
    isAnd: boolean,
};
type Filters = ReadonlyMap<string, Filter>

export default class FiltersManager {
    private readonly $filters: Filters;

    constructor() {
        // TODO: Restore state

        const $filters = new Map<string, Filter>();

        requireJQ('#filtersModal .filter-ctrl', 1, null).each((_, element) => {
            const $container = jQuery(element);
            const filterName: string = $container.data('filter-name');
            const isAnd: boolean = $container.data('is-and') === 'is-and';

            const $checkboxes = requireJQ(`#filters input[name="${filterName}[]"]`, 1, null) as JQuery<HTMLInputElement>;
            const $removeButton = $container.find('.filter-ctrl-remove');

            $filters.set(filterName, {
                removeButton: $removeButton,
                statusSpan: $container.find('.filter-ctrl-status'),
                checkboxes: $checkboxes,
                isAnd: isAnd,
            });

            $checkboxes.on('change', () => { this.refresh(filterName); });
            $removeButton.on('click', () => {
                $checkboxes.prop('checked', false);
                this.refresh(filterName);
            });
        });

        this.$filters = $filters;

        this.refresh();
    }

    private refresh(filterName: string|null = null): void {
        this.$filters.forEach((filter, key) => {
            if (filterName !== null && filterName !== key) {
                return;
            }

            const selected = filter.checkboxes
                .filter((_, element) => element.checked)
                .map((_, element) => element.dataset['label'] || element.value)
                .toArray();

            toggle(filter.removeButton, selected.length > 0, 0);
            filter.statusSpan.html(this.getStatusDescription(key, selected));
        });
    }

    private getStatusDescription(filterName: string, selected: string[]): string {
        if (selected.length === 0) {
            return filterName === 'inactive' ? 'Skip' : 'Any';
        }

        const allOrAny = filterName === 'features' ? 'all' : 'any';

        if (selected.length > 3) {
            return `${allOrAny} of ${selected.length} selected`;
        }

        const selectedList = selected.join(', ');

        if (selected.length === 1) {
            return selectedList;
        }

        return `${allOrAny} of: ${selectedList}`;
    }
}
