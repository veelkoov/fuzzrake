'use strict';

import * as $ from 'jquery';
import * as Consts from './consts';
import * as Utils from './utils';
import Artisan from './Artisan';
import Filter from './Filter';

let $dataTable;
let filters: object = {};

declare var DATA_UPDATES_URL: string;
declare var ARTISANS: Artisan[];

function refreshResults() {
    $dataTable.draw();
}

function getNewValObj(action: string): any {
    switch (action) {
        case 'none':
            return false;
        case 'all':
            return true;
        case 'invert':
            return (_, checked) => !checked;
        default:
            throw new Error();
    }
}

function initCheckBoxesFilter(selector: string, dataColumnIndex: number, isAnd: boolean) {
    filters[selector] = new Filter(dataColumnIndex, selector, isAnd, refreshResults);

    $.fn.dataTable.ext.search.push(filters[selector].getDataTableFilterCallback());

    $(`${selector} a`).each((_, element) => {
        let $a = $(element);
        let $checkboxes = $a.parents('fieldset').find('input:checkbox');
        let newValObj: any = getNewValObj($a.data('action'));
        let filter: Filter = filters[selector];

        $a.on('click', function (event, __) {
            event.preventDefault();

            $checkboxes.prop('checked', newValObj);
            filter.updateSelection();
        });
    });
}

function initDataTable(): void {
    $dataTable = $('#artisans').DataTable({
        dom:
            "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        paging: false,
        autoWidth: false,
        columnDefs: [
            { targets: 'no-sorting', orderable: false },
            { targets: 'default-hidden', visible: false } // , // FIXME
            // { targets: NAME_COLUMN_IDX, searchable: true }, // FIXME
            // { targets: '_all', searchable: false } // FIXME
        ],
        buttons: [{
            className: 'btn-sm btn-dark',
            columns: '.toggleable',
            extend: 'colvis',
            text: 'Show/hide columns'
        }],
        infoCallback: (settings, start, end, max, total, pre) =>
            `<p class="small">Displaying ${total} out of ${max} fursuit makers in the database</p>`
    });

    $('#artisans_wrapper .dt-buttons')
        .append(`<a class="btn btn-success btn-sm" href="${DATA_UPDATES_URL}">Studio missing?</a>`);
}

function processArtisansTable() {
    $('#artisans tr.fursuit-maker').each((index: number, item: object) => {
        $(item).data('artisan', ARTISANS[index]);
    });
}

export function init() {
    processArtisansTable();
    initDataTable();

    initCheckBoxesFilter('#countriesFilter', Consts.COUNTRY_COL_IDX, false);
    initCheckBoxesFilter('#stylesFilter', Consts.STYLES_COL_IDX, false);
    initCheckBoxesFilter('#featuresFilter', Consts.FEATURES_COL_IDX, true);
    initCheckBoxesFilter('#orderTypesFilter', Consts.TYPES_COL_IDX, false);
    initCheckBoxesFilter('#productionModelsFilter', Consts.PRODUCTION_MODEL_COL_IDX, false);

    $('#processingData, #dataProcessed').toggle();
}
