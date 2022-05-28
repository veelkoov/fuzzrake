import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import AgeAndSfwFilter from '../filters/data/AgeAndSfwFilter';
import AllSetUnOtFilter from '../filters/data/AllSetUnOtFilter';
import AnySetUnFilter from '../filters/data/AnySetUnFilter';
import AnySetUnOtFilter from '../filters/data/AnySetUnOtFilter';
import DataBridge from '../class/DataBridge';
import DataTablesFilterPlugin from '../filters/DataTablesFilterPlugin';
import FilterVisInterface from '../filters/ui/FilterVisInterface';
import GenericFilterVis from '../filters/ui/GenericFilterVis';
import OpenForFilter from '../filters/data/OpenForFilter';
import Species from '../species/Species';
import SpeciesFilterVis from '../filters/ui/SpeciesFilterVis';
import ValueUnFilter from '../filters/data/ValueUnFilter';
import {getAgeAndSfwConfig} from "./checklist";

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
    let badge = count > 0 ? ` <span class="badge rounded-pill bg-light text-dark">${count}</span>` : '';

    $filtersShowButton.html(`Filters${badge}`);
}

function setupSpeciesFiltersToggleButtons(): void {
    jQuery('#filtersModal .specie .toggle').on('click', function () {
        jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
    });
}

function initFilters(): void {
    filters.push(new GenericFilterVis<string>('countries', new ValueUnFilter('country')));
    filters.push(new GenericFilterVis<string>('states', new ValueUnFilter('state')));
    filters.push(new GenericFilterVis<string>('paymentPlans', new ValueUnFilter('filterPayPlans')));
    filters.push(new GenericFilterVis<string>('styles', new AnySetUnOtFilter('styles')));
    filters.push(new GenericFilterVis<string>('features', new AllSetUnOtFilter('features')));
    filters.push(new GenericFilterVis<string>('orderTypes', new AnySetUnOtFilter('orderTypes')));
    filters.push(new GenericFilterVis<string>('productionModels', new AnySetUnFilter('productionModels')));
    filters.push(new GenericFilterVis<string>('languages', new AnySetUnFilter('languages')));
    filters.push(new GenericFilterVis<boolean>('commissionsStatus', new OpenForFilter('openFor')));
    filters.push(new SpeciesFilterVis('species', 'speciesDoesFilters', 'speciesDoesntFilters', Species.get()));
    filters.push(new GenericFilterVis<string>('worksWithMinors', new ValueUnFilter('safeWorksWithMinors')));

    let dataFilters = filters.map(value => value.getFilter());
    dataFilters.push(new AgeAndSfwFilter(getAgeAndSfwConfig()));

    let filterDtPlugin = new DataTablesFilterPlugin(DataBridge.getArtisans(), dataFilters, AgeAndSfwConfig.getInstance());
    jQuery.fn.dataTable.ext.search.push(filterDtPlugin.getCallback());
    $filtersShowButton = jQuery('#filtersButton');
    jQuery('#filtersModal').on('hidden.bs.modal', applyFilters);

    setupSpeciesFiltersToggleButtons();
}

function restoreFilters(): void {
    for (let filter of filters) {
        filter.restoreChoices();
    }
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

export function init(): (() => void)[] {
    return [
        () => {
            initFilters();
        },
        () => {
            restoreFilters();
        },
    ];
}
