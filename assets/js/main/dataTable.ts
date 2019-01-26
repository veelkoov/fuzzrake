'use strict';

import * as $ from 'jquery';
import * as Consts from './consts';
import * as Utils from './utils';
import Artisan from './Artisan';

let artisans: Artisan[];
let $dataTable;
let filters: object;

declare var DATA_UPDATES_URL: string;

function refresh(_) {
    $.each(filters, function (_, filter: object) {
        filter['selectedValues'] = filter['$checkboxes']
            .filter(':checked')
            .map(function () {
                return $(this).val();
            })
            .get();
    });

    $dataTable.draw();
}

function getDataTableFilterFunction(filter, isAnd) {
    return function (_, data, __) {
        let selectedCount = filter['selectedValues'].length;

        if (selectedCount === 0) {
            return true;
        }

        let showUnknown = filter['selectedValues'].indexOf('') !== -1;

        if (showUnknown && data[filter['dataColumnIndex']].trim() === '') {
            return true;
        }

        let selectedNoUnknownCount = showUnknown ? selectedCount - 1 : selectedCount;
        let count = 0;

        data[filter['dataColumnIndex']].split(',').forEach(function (value, _, __) {
            if (filter['selectedValues'].indexOf(value.trim()) !== -1) {
                count++;
            }
        });

        return count > 0 && (!isAnd || count === selectedNoUnknownCount);
    };
}

function initCheckBoxesFilter(selector: string, dataColumnIndex: number, isAnd: boolean) {
    filters[selector] = {
        dataColumnIndex: dataColumnIndex,
        $checkboxes: $(`${selector} input[type=checkbox]`),
        selectedValues: []
    };

    filters[selector].$checkboxes.on('change', refresh);

    $.fn.dataTable.ext.search.push(getDataTableFilterFunction(filters[selector], isAnd));
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

function clonePrimaryLinksForDropdown($links) {
    let result = $links.filter('.primary').clone().addClass('btn btn-secondary');

    result.contents().filter(function () {
        return this.nodeType === 3; // text node
    }).remove();

    return result;
}

function processList($row: any, otherItems: string[], columnIndex: number) {
    $row.children().eq(columnIndex).html((index, oldHtml) => oldHtml.toString().replace(/\n/g, ', '));

    if (otherItems.length > 0) {
        $row.children().eq(columnIndex).html((index, oldHtml) => `${oldHtml}${oldHtml ? ', ' : ''}Other`);
    }
}

function processRowHtml($row: any, artisan: Artisan): void {
    $row.children().eq(Consts.NAME_COL_IDX).html(artisan.name + Utils.countryFlagHtml(artisan.country));

    if (artisan.areCommissionsOpen !== null) {
        $row.children().eq(Consts.COMMISSIONS_COL_IDX).html(
            artisan.areCommissionsOpen
                ? '<i class="fas fa-check-circle"></i> Open'
                : '<i class="fas fa-times-circle"></i> Closed');
    }

    processList($row, [], Consts.PRODUCTION_MODEL_COL_IDX);
    processList($row, artisan.otherStyles, Consts.STYLES_COL_IDX);
    processList($row, artisan.otherTypes, Consts.TYPES_COL_IDX);
    processList($row, artisan.otherFeatures, Consts.FEATURES_COL_IDX);

    $row.children().eq(Consts.LINKS_COL_IDX).html(`
        <div class="btn-group artisan-links" role="group" aria-label="Dropdown with links to websites">
            <div class="btn-group" role="group">
                <button id="drpdwnmn${$row.index()}" type="button" class="btn btn-secondary dropdown-toggle"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-boundary="viewport"
                    data-flip="false"></button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="drpdwnmn${$row.index()}">
                    <a class="dropdown-item request-update" href="#" data-toggle="modal" data-target="#updateRequestModal">
                        <i class="fas fa-exclamation-triangle"></i> Request update
                    </a>
    `);

    let $links = Utils.getLinks$(artisan);
    $row.find('.artisan-links .btn-group').prepend(clonePrimaryLinksForDropdown($links));
    $row.find('.artisan-links .dropdown-menu').prepend($links.addClass('dropdown-item'));
}

function initSelectionLinksAllNoneInvert() {
    $('#filtersAccordion a').on('click', function (event, sth) {
        let $checkboxes = $(event.target).parents('fieldset').find('input:checkbox');

        switch ($(this).data('action')) {
            case 'none':
                $checkboxes.prop('checked', false);
                break;
            case 'all':
                $checkboxes.prop('checked', true);
                break;
            case 'invert':
                $checkboxes.prop("checked", function (_, checked) {
                    return !checked;
                });
                break;
        }

        refresh(null);
        event.preventDefault();
    });
}

export function init() {
    artisans = [];
    filters = {};

    $('#artisans tr.fursuit-maker').each((_: number, item: object) => {
        let $row = $(item);
        let artisan = Artisan.fromArray($row.children().toArray().map((value: any) => value.innerHTML));

        $row.data('artisan', artisan);
        artisans.push(artisan);

        processRowHtml($row, artisan);
    });

    initDataTable();

    initCheckBoxesFilter('#countriesFilter', Consts.COUNTRY_COL_IDX, false);
    initCheckBoxesFilter('#stylesFilter', Consts.STYLES_COL_IDX, false);
    initCheckBoxesFilter('#featuresFilter', Consts.FEATURES_COL_IDX, true);
    initCheckBoxesFilter('#orderTypesFilter', Consts.TYPES_COL_IDX, false);
    initCheckBoxesFilter('#productionModelsFilter', Consts.PRODUCTION_MODEL_COL_IDX, false);

    initSelectionLinksAllNoneInvert();
}
