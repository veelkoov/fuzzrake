var $dataTable;
var filters = {};

const NAME_COLUMN_IDX = 0;
const COUNTRIES_COLUMN_IDX = 1;
const STYLES_COLUMN_IDX = 2;
const FEATURES_COLUMN_IDX = 3;

$(document).ready(function () {
    initDataTable();
    initSearchForm();

    $('#artisans a').click(function (evt) {
        evt.preventDefault();
        window.open(this.href);
    });

    $('div.artisan-links').attr('title','If you\'re going to contact the studio/maker, <u>please let them know you found them here!</u> This will help us all a lot. Thank you!')
        .data('placement', 'top')
        .data('boundary', 'window')
        .data('html', true)
        .data('fallbackPlacement', [])
        .tooltip();
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

    $('#artisanDetailsModal').on('show.bs.modal', function (event) {
        var $row = $(event.relatedTarget).closest('tr');

        $('#artisanName').html($row.children().eq(NAME_COLUMN_IDX).html());
        $('#artisanLocation').html([$row.data('state'), $row.data('city')].filter(i => i).join(', ') || '<i class="fas fa-question-circle" title="Where are you?"></i>');
        $('#artisanFeatures').html(htmlListFromCommaSeparated($row.children().eq(FEATURES_COLUMN_IDX).text()));
        $('#artisanTypes').html(htmlListFromCommaSeparated($row.data('types')));
        $('#artisanStyles').html(htmlListFromCommaSeparated($row.children().eq(STYLES_COLUMN_IDX).text()));
        $('#artisanSince').html($row.data('since') || '<i class="fas fa-question-circle" title="How long?"></i>');
    });
}

function htmlListFromCommaSeparated(input) {
    return input ? '<ul><li>' + input.split(', ').join('</li><li>') + '</li></ul>' : '<i class="fas fa-question-circle"></i>';
}

function initSearchForm() {
    addChoiceWidget('#countriesFilter', COUNTRIES_COLUMN_IDX, false, countriesOnCreateTemplatesCallback);
    addChoiceWidget('#stylesFilter', STYLES_COLUMN_IDX, false);
    addChoiceWidget('#featuresFilter', FEATURES_COLUMN_IDX, true);
}

function addChoiceWidget(selector, dataColumnIndex, isAnd, onCreateTemplatesCallback = null) {
    filters[selector] = {
        selectObj: new Choices(selector, {
            shouldSort: false,
            removeItemButton: true,
            callbackOnCreateTemplates: onCreateTemplatesCallback
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
