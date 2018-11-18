'use strict';

import $ from 'jquery';
import Choices from "../../3rd-party/Choices/public/assets/scripts/choices";
import isMobile from './isMobile'
import * as consts from './consts';

require('../../3rd-party/Choices/public/assets/styles/choices.css');

let $dataTable;
let filters = {};

function refresh(_) {
    $.each(filters, function (_, filter) {
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
            return template(`<div class="${classNames.item} ${data.highlighted ? classNames.highlightedState : classNames.itemSelectable}" data-item data-id="${data.id}" data-value="${data.value}" ${data.active ? 'aria-selected="true"' : ''} ${data.disabled ? 'aria-disabled="true"' : ''}> ${data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label.replace(/^[A-Z]+ /, '') : data.label}</div>`);
        },
        choice: function choice(classNames, data) {
            return template(`<div class="${classNames.item} ${classNames.itemChoice} ${data.disabled ? classNames.itemDisabled : classNames.itemSelectable}" data-select-text="${_this.config.itemSelectText}" data-choice ${data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable'} data-id="${data.id}" data-value="${data.value}" ${data.groupId > 0 ? 'role="treeitem"' : 'role="option"'}> ${data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label : data.label}</div>`);
        }
    };
}

function initSelectFilter(selector, dataColumnIndex, forceOnMobile, isAnd, onCreateTemplatesCallback) {
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

export default function initDataTable() {
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
        infoCallback: function infoCallback(settings, start, end, max, total, pre) {
            return '<p class="small">Displaying ' + total + ' out of ' + max + ' fursuit makers in the database</p>';
        }
    });

    $('#artisans_wrapper .dt-buttons')
        .append('<a class="btn btn-success btn-sm" href="' + DATA_UPDATES_URL + '">Studio missing?</a>');

    initSelectFilter('#countriesFilter', consts.COUNTRIES_COLUMN_IDX, true, false, countriesOnCreateTemplatesCallback);
    initSelectFilter('#stylesFilter', consts.STYLES_COLUMN_IDX, false, false);
    initSelectFilter('#featuresFilter', consts.FEATURES_COLUMN_IDX, false, true);
}
