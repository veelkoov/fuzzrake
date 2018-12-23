'use strict';

import * as Choices from "../../3rd-party/Choices/public/assets/scripts/choices";
import * as $ from 'jquery';
import * as Consts from './consts';
import * as Utils from './utils';
import isMobile from './isMobile';
import Artisan from './Artisan';

require('../../3rd-party/Choices/public/assets/styles/choices.css');

let artisans: Artisan[];
let $dataTable;
let filters: object;

declare var DATA_UPDATES_URL: string;

function refresh(_) {
    $.each(filters, function (_, filter: object) {
        filter['selectedValues'] = filter['$select'].val();
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

function countriesOnCreateTemplatesCallback(template) {
    let _this = this;
    let classNames = this.config.classNames;

    return {
        item: function item(classNames, data) {
            return template(`<div class="${classNames.item} ${data.highlighted ? classNames.highlightedState : classNames.itemSelectable}" data-item data-id="${data.id}" data-value="${data.value}" ${data.active ? 'aria-selected="true"' : ''} ${data.disabled ? 'aria-disabled="true"' : ''}> ${data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value.toLowerCase() + '"></span> ' + data.label.replace(/^[A-Z]+ /, '') : data.label}</div>`);
        },
        choice: function choice(classNames, data) {
            return template(`<div class="${classNames.item} ${classNames.itemChoice} ${data.disabled ? classNames.itemDisabled : classNames.itemSelectable}" data-select-text="${_this.config.itemSelectText}" data-choice ${data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable'} data-id="${data.id}" data-value="${data.value}" ${data.groupId > 0 ? 'role="treeitem"' : 'role="option"'}> ${data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value.toLowerCase() + '"></span> ' + data.label : data.label}</div>`);
        }
    };
}

function initSelectFilter(selector: string, dataColumnIndex: number, forceOnMobile: boolean, isAnd: boolean, onCreateTemplatesCallback?: (any) => object) {
    let useChoices = !isMobile() || forceOnMobile;

    let selectObj = useChoices ? new Choices(selector, {
        shouldSort: false,
        removeItemButton: true,
        callbackOnCreateTemplates: onCreateTemplatesCallback,
        itemSelectText: ''
    }) : null;

    filters[selector] = {
        selectObj: selectObj,
        dataColumnIndex: dataColumnIndex,
        $select: $(selector),
        selectedValues: []
    };

    filters[selector].$select[0].addEventListener('change', refresh);

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

    initSelectFilter('#countriesFilter', Consts.COUNTRY_COL_IDX, true, false, countriesOnCreateTemplatesCallback);
    initSelectFilter('#stylesFilter', Consts.STYLES_COL_IDX, false, false);
    initSelectFilter('#featuresFilter', Consts.FEATURES_COL_IDX, false, true);
}
