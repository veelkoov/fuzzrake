import {requireJQ, toggle} from '../jQueryUtils';
import Storage from '../class/Storage';

type Filter = {
    removeButton: JQuery<HTMLElement>,
    statusSpan: JQuery<HTMLElement>,
    checkboxes: JQuery<HTMLInputElement>,
    isAnd: boolean,
};
type Filters = ReadonlyMap<string, Filter>

export default class FiltersManager {
    private readonly labelsToLowercase: ReadonlySet<string> = new Set<string>([
        'Unknown',         // grep-special-label-unknown
        'Show',            // grep-special-label-show-inactive
        'Not tracked',     // grep-special-label-not-tracked
        'Tracking issues', // grep-special-label-tracking-issues
    ]);

    private readonly $filters: Filters;

    constructor() {
        const $filters = new Map<string, Filter>();

        requireJQ('#filtersModal .filter-ctrl', 1, null).each((_, element) => {
            const $container = jQuery(element);
            const filterName: string = $container.data('filter-name');
            const isAnd: boolean = $container.data('is-and') === 'is-and';

            const $checkboxes = requireJQ(`#filters input[name="${filterName}[]"]`, 1, null) as JQuery<HTMLInputElement>;
            const $removeButton = $container.find('.filter-ctrl-remove');

            const stored: string = Storage.getString(`filters/${filterName}/choices`, '');

            if (stored) {
                const values = new Set<string>(stored.split('\n'));

                $checkboxes.each((_, element) => {
                    if (values.has(element.value)) {
                        element.checked = true;
                    }
                });
            }

            $filters.set(filterName, {
                removeButton: $removeButton,
                statusSpan: $container.find('.filter-ctrl-status'),
                checkboxes: $checkboxes,
                isAnd: isAnd,
            });

            $checkboxes.on('change', () => { this.update(filterName); });
            $removeButton.on('click', () => {
                $checkboxes.prop('checked', false);
                this.update(filterName);
            });
        });

        this.$filters = $filters;

        this.update();
    }

    private update(filterName: string|null = null): void {
        this.$filters.forEach((filter, key) => {
            if (filterName !== null && filterName !== key) {
                return;
            }

            const selected = filter.checkboxes
                .filter((_, element) => element.checked);
            const selectedLabels = selected
                .map((_, element) => element.dataset['label'] || element.value)
                .toArray();
            const selectedValues = selected
                .map((_, element) => element.value)
                .toArray();

            Storage.saveString(`filters/${key}/choices`, selectedValues.join('\n'));

            toggle(filter.removeButton, selected.length > 0, 0);
            filter.statusSpan.html(this.getStatusDescription(key, selectedLabels));
        });
    }

    private getStatusDescription(filterName: string, selected: string[]): string {
        if (selected.length === 0) {
            return filterName === 'inactive' ? 'skip' : 'any';
        }

        const allOrAny = filterName === 'features' ? 'all' : 'any';

        if (selected.length > 3) {
            return `${allOrAny} of ${selected.length} selected`;
        }

        const selectedList = selected.map((item) => this.fixLabelForDescription(item)).sort().join(', ');

        if (selected.length === 1) {
            return selectedList;
        }

        return `${allOrAny} of: ${selectedList}`;
    }

    private fixLabelForDescription(item: string): string {
        const removedExplanation = item.replace(/ \(.+?\)$/, ''); // FIXME: #171 Glossary

        if (this.labelsToLowercase.has(removedExplanation)) {
            return removedExplanation.toLowerCase();
        }

        return removedExplanation;
    }
}
