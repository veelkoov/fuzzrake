'use strict';

import * as $ from 'jquery';
import Artisan from './Artisan';
import Filter from './Filter';
import FilterSimpleValue from "./FilterSimpleValue";
import FilterSetSingle from "./FilterSetSingle";
import FilterSetWithOthers from "./FilterSetWithOthers";

let $dataTable;
let filters: object = {};

declare var DATA_UPDATES_URL: string;
declare var ARTISANS: Artisan[];

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
        .getDataTableFilterCallback(ARTISANS));

    initCheckBoxesMultiswitches(filter.containerSelector);
}

function initDataTable(): void {
    $dataTable = $('#artisans').DataTable({
        dom:
            "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col'ip>>",
        paging: false,
        autoWidth: false,
        columnDefs: [
            { targets: 'no-sorting', orderable: false },
            { targets: 'default-hidden', visible: false },
            { targets: 'maker-id', searchable: true },
            { targets: 'name', searchable: true },
            { targets: 'searchable', searchable: true },
            { targets: '_all', searchable: false },
        ],
        buttons: [{
            className: 'btn btn-dark',
            columns: '.toggleable',
            extend: 'colvis',
            text: 'Choose columns'
        }],
        infoCallback: (settings, start, end, max, total, _) =>
            `<p class="small">Displaying ${total} out of ${max} fursuit makers in the database. &nbsp;
                <a href="${DATA_UPDATES_URL}"><span class="badge badge-warning">Studio missing?</span></a>
            </p>`
    });

    $('#artisans_wrapper .dt-buttons')
        .append(`<button type="button" class="btn btn-success" data-toggle="modal" data-target="#filtersModal">Choose filters</button>`);
}

function processArtisansTable() {
    $('#artisans tr.fursuit-maker').each((index: number, item: object) => {
        $(item).data('artisan', ARTISANS[index]);
    });
}

export function init() {
    processArtisansTable();
    initDataTable();

    addFilter(new FilterSimpleValue  ('country',           '#countriesFilter',           $dataTable.draw));
    addFilter(new FilterSetWithOthers('styles',            '#stylesFilter',              $dataTable.draw, false));
    addFilter(new FilterSetWithOthers('features',          '#featuresFilter',            $dataTable.draw, true));
    addFilter(new FilterSetWithOthers('orderTypes',        '#orderTypesFilter',          $dataTable.draw, false));
    addFilter(new FilterSetSingle    ('productionModels',  '#productionModelsFilter',    $dataTable.draw, false));
    addFilter(new FilterSimpleValue  ('commissionsStatus', '#commissionsStatusesFilter', $dataTable.draw));
}
