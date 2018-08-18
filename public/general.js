var $dataTable;
var filters = {};

const NAME_COLUMN_IDX = 0;
const COUNTRIES_COLUMN_IDX = 1;
const STYLES_COLUMN_IDX = 2;
const FEATURES_COLUMN_IDX = 3;

$(document).ready(function () {
    initDataTable();
    initDetailsModal();
    initSearchForm();
    addReferrerRequestTooltip();
});

function initDataTable() {
    $dataTable = $('#artisans').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        paging: false,
        autoWidth: false,
        columnDefs: [
            {targets: 'no-sorting', orderable: false}
        ],
        buttons: [
            {
                className: 'btn-sm btn-dark',
                columns: '.toggleable',
                extend: 'colvis',
                text: 'Show/hide columns',
            }
        ],
        infoCallback: function( settings, start, end, max, total, pre ) {
            return '<p class="small">Displaying ' + total + ' out of ' + max + ' fursuit makers in the database</p>';
        }
    });

    $('#artisans a').click(function (evt) {
        evt.preventDefault();
        window.open(this.href);
    });
}

function initDetailsModal() {
    $('#artisanDetailsModal').on('show.bs.modal', function (event) {
        var $row = $(event.relatedTarget).closest('tr');

        $('#artisanName').html($row.children().eq(NAME_COLUMN_IDX).html());
        $('#artisanLocation').html(formatLocation($row.data('state'), $row.data('city')));
        $('#artisanFeatures').html(htmlListFromCommaSeparated($row.children().eq(FEATURES_COLUMN_IDX).text(), $row.data('other-features')));
        $('#artisanTypes').html(htmlListFromCommaSeparated($row.data('types'), $row.data('other-types')));
        $('#artisanStyles').html(htmlListFromCommaSeparated($row.children().eq(STYLES_COLUMN_IDX).text(), $row.data('other-styles')));
        $('#artisanSince').html(formatSince($row.data('since')));
        // $('#artisanLinks').html($row.data('since') || '<i class="fas fa-question-circle" title="How long?"></i>');
        $('#artisanCommissionsStatus').html(commissionsStatusFromArtisanRowData($row.data('commissions-status'), $row.data('cst-last-check'), $row.data('cst-url')));
    });
}

function initSearchForm() {
    addChoiceWidget('#countriesFilter', COUNTRIES_COLUMN_IDX, false, countriesOnCreateTemplatesCallback);
    addChoiceWidget('#stylesFilter', STYLES_COLUMN_IDX, false);
    addChoiceWidget('#featuresFilter', FEATURES_COLUMN_IDX, true);
}

function addReferrerRequestTooltip() {
    $('div.artisan-links').attr('title', 'If you\'re going to contact the studio/maker, <u>please let them know you found them here!</u> This will help us all a lot. Thank you!')
        .data('placement', 'top')
        .data('boundary', 'window')
        .data('html', true)
        .data('fallbackPlacement', [])
        .tooltip();
}

function htmlListFromCommaSeparated(list, other) {
    var listLis = list !== '' ? '<li>' + list.split(', ').join('</li><li>') + '</li>' : '';
    var otherLis = other !== '' ? '<li>Other: ' + other + '</li>' : '';

    return listLis + otherLis ? '<ul>' + listLis + otherLis + '</ul>' : '<i class="fas fa-question-circle"></i>';
}

function addChoiceWidget(selector, dataColumnIndex, isAnd, onCreateTemplatesCallback = null) {
    filters[selector] = {
        selectObj: new Choices(selector, {
            shouldSort: false,
            removeItemButton: true,
            callbackOnCreateTemplates: onCreateTemplatesCallback,
            itemSelectText: ''
        }),
        dataColumnIndex: dataColumnIndex,
        $select: $(selector),
        selectedValues: []
    };

    filters[selector]['selectObj'].passedElement.addEventListener('addItem', refresh);
    filters[selector]['selectObj'].passedElement.addEventListener('removeItem', refresh);

    $.fn.dataTable.ext.search.push(getDataTableFilterFunction(filters[selector], isAnd));
}

function refresh(_) {
    $.each(filters, function(_, filter) {
        filter['selectedValues'] = filter['$select'].val();
    });

    $dataTable.draw();
}

function getDataTableFilterFunction(filter, isAnd) {
    return function (_, data, _) {
        var selectedCount = filter['selectedValues'].length;

        if (selectedCount === 0) {
            return true;
        }

        var showUnknown = filter['selectedValues'].indexOf('') !== -1;

        if (showUnknown && data[filter['dataColumnIndex']].trim() === '') {
            return true;
        }

        var selectedNoUnknownCount = showUnknown ? selectedCount - 1 : selectedCount;
        var count = 0;

        data[filter['dataColumnIndex']].split(',').forEach(function (value, _, _) {
            if (filter['selectedValues'].indexOf(value.trim()) !== -1) {
                count++;
            }
        });

        return count > 0 && (!isAnd || count === selectedNoUnknownCount)
    }
}

function countriesOnCreateTemplatesCallback(template) {
    var classNames = this.config.classNames;

    return {
        item: (data) => {
            return template(`
                <div class="${classNames.item} ${data.highlighted ? classNames.highlightedState : ''} ${!data.disabled ? classNames.itemSelectable : ''}" data-item data-id="${data.id}" data-value="${data.value}" ${data.active ? 'aria-selected="true"' : ''} ${data.disabled ? 'aria-disabled="true"' : ''} data-deletable>${data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label.replace(/^[A-Z]+ /, '') : data.label}<button class="${classNames.button}" data-button>Remove item</button></div>
            `);
        },
        choice: (data) => {
            return template(`
                <div class="${classNames.item} ${classNames.itemChoice} ${data.disabled ? classNames.itemDisabled : classNames.itemSelectable}" data-select-text="${this.config.itemSelectText}" data-choice ${data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable'} data-id="${data.id}" data-value="${data.value}" ${data.groupId > 0 ? 'role="treeitem"' : 'role="option"'}>${data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label : data.label}</div>
            `);
        },
    };
}

function commissionsStatusFromArtisanRowData(commissionsStatusData, cstLastCheck, cstUrl) {
    var commissionsStatus = commissionsStatusData === '' ? 'unknown' : (commissionsStatusData ? 'open' : 'closed');

    if (cstUrl === '') {
        return 'Commissions are <strong>' + commissionsStatus + '</strong>.' +
            ' Status is not automatically tracked and updated.' +
            ' <a href="./info.html#commissions-status-tracking">Learn more</a>';
    }

    if (commissionsStatusData === '') {
        return 'Commissions status is unknown. It should be tracked and updated automatically from this web page:' +
            ' <a href="' + cstUrl + '">' + cstUrl + '</a>, however our software failed to "understand"' +
            ' the status based on the page contents. Last time it tried on ' + cstLastCheck +
            ' UTC. <a href="./info.html#commissions-status-tracking">Learn more</a>';
    }

    return 'Commissions are <strong>' + commissionsStatus + '</strong>. Status is tracked and updated' +
        ' automatically from this web page: <a href="' + cstUrl + '">' + cstUrl + '</a>.'
        + ' Last time checked on ' + cstLastCheck + ' UTC.' +
        ' <a href="./info.html#commissions-status-tracking">Learn more</a>';
}

function formatLocation(state, city) {
    return [state, city].filter(i => i).join(', ') || '<i class="fas fa-question-circle" title="Where are you?"></i>';
}

function formatSince(since) {
    return since || '<i class="fas fa-question-circle" title="How long?"></i>';
}
