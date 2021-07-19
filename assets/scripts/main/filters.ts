import AllSetUnOtFilter from "../filters/data/AllSetUnOtFilter";
import AnySetUnFilter from "../filters/data/AnySetUnFilter";
import AnySetUnOtFilter from "../filters/data/AnySetUnOtFilter";
import DataBridge from "../class/DataBridge";
import DataTablesFilterPlugin from "../filters/DataTablesFilterPlugin";
import FilterVisInterface from "../filters/ui/FilterVisInterface";
import GenericFilterVis from "../filters/ui/GenericFilterVis";
import OpenForFilter from "../filters/data/OpenForFilter";
import Species from "../species/Species";
import SpeciesFilterVis from "../filters/ui/SpeciesFilterVis";
import ValueUnFilter from "../filters/data/ValueUnFilter";

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

function setupSpeciesFiltersToggleButtons(): void {
    jQuery('#filtersModal .specie .toggle').on('click', function () {
        jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
    });
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
    filters.push(new GenericFilterVis<string>('countries', new ValueUnFilter('country')));
    filters.push(new GenericFilterVis<string>('states', new ValueUnFilter('state')));
    filters.push(new GenericFilterVis<string>('styles', new AnySetUnOtFilter('styles')));
    filters.push(new GenericFilterVis<string>('features', new AllSetUnOtFilter('features')));
    filters.push(new GenericFilterVis<string>('orderTypes', new AnySetUnOtFilter('orderTypes')));
    filters.push(new GenericFilterVis<string>('productionModels', new AnySetUnFilter('productionModels')));
    filters.push(new GenericFilterVis<string>('languages', new AnySetUnFilter('languages')));
    filters.push(new GenericFilterVis<boolean>('commissionsStatus', new OpenForFilter('openFor')));
    filters.push(new SpeciesFilterVis('species', 'speciesDoesFilters', 'speciesDoesntFilters', Species.get()));

    let filterDtPlugin = new DataTablesFilterPlugin(DataBridge.getArtisans(), filters);
    jQuery.fn.dataTable.ext.search.push(filterDtPlugin.getCallback());
    $filtersShowButton = jQuery('#filtersButton');
    jQuery('#filtersModal').on('hidden.bs.modal', applyFilters);

    setupSpeciesFiltersToggleButtons();
}

export function restoreFilters(): void {
    for (let filter of filters) {
        filter.restoreChoices();
    }
}
