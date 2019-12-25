import * as $ from "jquery";
import Filter from "../class/Filter";
import DataBridge from "../class/DataBridge";
import FilterSimpleValue from "../class/FilterSimpleValue";
import FilterSetWithOthers from "../class/FilterSetWithOthers";
import FilterSetSingle from "../class/FilterSetSingle";

let filters: object = {};

function getCheckedValueFunction(action: string): any {
    switch (action) {
        case 'none':
            return false; // "function"
        case 'all':
            return true; // "function"
        case 'invert':
            return (_, checked) => !checked;
        default:
            throw new Error();
    }
}

function initCheckBoxesMultiswitches(containerSelector: string) {
    $(`${containerSelector} a`).each((_, element) => {
        let $a = $(element);
        let $checkboxes = $a.parents('fieldset').find('input:checkbox');
        let checkedValueFunction: any = getCheckedValueFunction($a.data('action'));
        let filter: Filter = filters[containerSelector];

        $a.on('click', function (event, __) {
            event.preventDefault();

            $checkboxes.prop('checked', checkedValueFunction);
            filter.updateSelection();
        });
    });
}

function addFilter(filter: Filter) {
    filters[filter.containerSelector] = filter;

    $.fn.dataTable.ext.search.push(filters[filter.containerSelector]
        .getDataTableFilterCallback(DataBridge.getArtisans()));

    initCheckBoxesMultiswitches(filter.containerSelector);
}

function initFilters(refreshCallback: () => void) {
    addFilter(new FilterSimpleValue  ('country',           '#countriesFilter',           refreshCallback));
    addFilter(new FilterSetWithOthers('styles',            '#stylesFilter',              refreshCallback, false));
    addFilter(new FilterSetWithOthers('features',          '#featuresFilter',            refreshCallback, true));
    addFilter(new FilterSetWithOthers('orderTypes',        '#orderTypesFilter',          refreshCallback, false));
    addFilter(new FilterSetSingle    ('productionModels',  '#productionModelsFilter',    refreshCallback, false));
    addFilter(new FilterSetSingle    ('languages',         '#languagesFilter',           refreshCallback, false));
    addFilter(new FilterSimpleValue  ('commissionsStatus', '#commissionsStatusesFilter', refreshCallback));
}

export { initFilters }
