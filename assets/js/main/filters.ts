import * as $ from "jquery";
import Filter from "../class/Filter";
import DataBridge from "../class/DataBridge";
import FilterSimpleValue from "../class/FilterSimpleValue";
import FilterSetWithOthers from "../class/FilterSetWithOthers";
import FilterSetSingle from "../class/FilterSetSingle";

let filters: { [id: string]: Filter } = {};
let $filtersButton: JQuery<HTMLElement>;
let refreshList: () => void;

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

function initCheckBoxesMultiswitches(filter: Filter): void {
    $(`${filter.getBodySelector()} a`).each((_, element) => {
        let $a = $(element);
        let $checkboxes = $a.parents('fieldset').find('input:checkbox');
        let checkedValueFunction: any = getCheckedValueFunction($a.data('action'));

        $a.on('click', function (event, __) {
            event.preventDefault();

            $checkboxes.prop('checked', checkedValueFunction);
            filter.updateSelection();
        });
    });
}

function addFilter(filter: Filter): void {
    filters[filter.getFieldName()] = filter;
    $.fn.dataTable.ext.search.push(filter.getDataTableFilterCallback(DataBridge.getArtisans()));
    initCheckBoxesMultiswitches(filter);
}

function refreshEverything(): void {
    let count: number = 0;

    for (let f in filters) {
        if (filters[f].hasAnyChoice()) {
            count++;
        }
    }

    $filtersButton.html('Choose filters' + (count > 0 ? ` <span class="badge badge-pill badge-light">${count}</span>` : ''));

    refreshList();
}

function initFilters(refreshCallback: () => void): void {
    refreshList = refreshCallback;

    addFilter(new FilterSimpleValue  ('country',           'countries'));
    addFilter(new FilterSimpleValue  ('state',             'states'));
    addFilter(new FilterSetWithOthers('styles',            'styles',              false));
    addFilter(new FilterSetWithOthers('features',          'features',            true));
    addFilter(new FilterSetWithOthers('orderTypes',        'orderTypes',          false));
    addFilter(new FilterSetSingle    ('productionModels',  'productionModels',    false));
    addFilter(new FilterSetSingle    ('languages',         'languages',           false));
    addFilter(new FilterSimpleValue  ('commissionsStatus', 'commissionsStatus'));

    $filtersButton = $('#filtersButton');
    $('#filtersModal').on('hidden.bs.modal', refreshEverything);
}

function restoreFilters(): void {
    for (let selector in filters) {
        filters[selector].restoreChoices();
    }
}

export { initFilters, restoreFilters, refreshEverything }
