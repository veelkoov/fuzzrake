import ValueFilterVis from "../filters/ui/ValueFilterVis";
import SetFilterVis from "../filters/ui/SetFilterVis";
import FilterVisInterface from "../filters/ui/FilterVisInterface";
import DataBridge from "../class/DataBridge";
import DataTablesFilterPlugin from "../filters/DataTablesFilterPlugin";

let filters: FilterVisInterface[] = [];
let $filtersShowButton: JQuery<HTMLElement>;
let refreshList: () => void = () => {};

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

export function setRefreshCallback(refreshCallback: () => void): void {
    refreshList = refreshCallback;
}

export function applyFilters(): void {
    refreshFiltersShowButton();
    refreshList();

    for (let filter of filters) {
        filter.saveChoices();
    }
}

export function initFilters(): void {
    filters.push(new ValueFilterVis<string>('countries', 'country'));
    filters.push(new ValueFilterVis<string>('states', 'state'));
    filters.push(new SetFilterVis<string>('styles', 'styles', false, true));
    filters.push(new SetFilterVis<string>('features', 'features', true, true));
    filters.push(new SetFilterVis<string>('orderTypes', 'orderTypes', false, true));
    filters.push(new SetFilterVis<string>('productionModels', 'productionModels', false, false));
    filters.push(new SetFilterVis<string>('languages', 'languages', false, false));
    filters.push(new ValueFilterVis<boolean>('commissionsStatus', 'commissionsStatus'));

    let filterDtPlugin = new DataTablesFilterPlugin(DataBridge.getArtisans(), filters);
    jQuery.fn.dataTable.ext.search.push(filterDtPlugin.getCallback());
    $filtersShowButton = jQuery('#filtersButton');
    jQuery('#filtersModal').on('hidden.bs.modal', applyFilters);
}

export function restoreFilters(): void {
    for (let filter of filters) {
        filter.restoreChoices();
    }
}
