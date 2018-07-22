var $dataTable;
var filters = {};
var FURSUITREVIEW_COLUMN_INDEX = 5; // TODO: fetch automatically
var FIRST_LINK_COLUMN_INDEX = 6; // TODO: fetch automatically

$(document).ready(function () {
    initDataTable();
    initSearchForm();
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
                columnText: function (_, columnIndex, defaultText) {
                    switch (columnIndex) {
                        case FURSUITREVIEW_COLUMN_INDEX:
                            return 'FursuitReview link';
                        case FIRST_LINK_COLUMN_INDEX:
                            return 'Websites links';
                        default:
                            return defaultText;
                    }
                }
            }
        ],
        infoCallback: function( settings, start, end, max, total, pre ) {
            return 'Displaying ' + total + ' out of ' + max + ' fursuit makers in the database';
        }
    });

    $dataTable.on('column-visibility.dt', function (_1, _2, columnIndex, state, _4) {
        if (columnIndex == FIRST_LINK_COLUMN_INDEX) {
            $dataTable.columns('.toggleable-link').visible(state);
        }
    });
}

function initSearchForm() {
    addChoiceWidget('#countriesFilter', 1, false, countriesOnCreateTemplatesCallback);
    addChoiceWidget('#typesFilter', 2, false);
    addChoiceWidget('#featuresFilter', 3, true);
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
