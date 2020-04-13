import ValueCategorizedFilterVis from "../filters/ui/ValueCategorizedFilterVis";
import ValueFilterVis from "../filters/ui/ValueFilterVis";
import SetFilterVis from "../filters/ui/SetFilterVis";
import FilterVisInterface from "../filters/ui/FilterVisInterface";
import DataBridge from "../class/DataBridge";

let filters: { [id: string]: FilterVisInterface } = {};
let $filtersShowButton: JQuery<HTMLElement>;
let refreshList: () => void = () => {};

function addFilter(filter: FilterVisInterface): void {
    filters[filter.getFilterId()] = filter;
    jQuery.fn.dataTable.ext.search.push(filter.getDataTableFilterCallback(DataBridge.getArtisans()));
}

export function setRefreshCallback(refreshCallback: () => void): void {
    refreshList = refreshCallback;
}

function countActiveFilters(): number {
    let count: number = 0;

    for (let filterId in filters) {
        if (filters[filterId].isActive()) {
            count++;
        }
    }

    return count;
}

function refreshFiltersShowButton(): void {
    let count = countActiveFilters();
    let badge = count > 0 ? ` <span class="badge badge-pill badge-light">${count}</span>` : '';

    $filtersShowButton.html(`Choose filters${badge}`);
}

export function refreshEverything(): void {
    refreshFiltersShowButton();
    refreshList();
}

export function initFilters(): void {
    addFilter(new ValueCategorizedFilterVis('countries', 'country'));
    addFilter(new ValueFilterVis('states', 'state'));
    addFilter(new SetFilterVis('styles', 'styles', false, true));
    addFilter(new SetFilterVis('features', 'features', true, true));
    addFilter(new SetFilterVis('orderTypes', 'orderTypes', false, true));
    addFilter(new SetFilterVis('productionModels', 'productionModels', false, false));
    addFilter(new SetFilterVis('languages', 'languages', false, false));
    addFilter(new ValueFilterVis('commissionsStatus', 'commissionsStatus'));

    $filtersShowButton = jQuery('#filtersButton');
    jQuery('#filtersModal').on('hidden.bs.modal', refreshEverything);
}

export function restoreFilters(): void {
    for (let selector in filters) {
        filters[selector].restoreChoices();
    }
}
