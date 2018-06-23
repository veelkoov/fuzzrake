var $dataTable;
var COUNTRY_COL_IDX = 1;
var TYPES_COL_IDX = 2;
var FEATURES_COL_IDX = 3;
var multiselectFilters = {};

$(document).ready(function () {
    $dataTable = $('#artisans').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        paging: false,
        buttons: [
            {
                className: 'btn-sm btn-dark',
                columns: '.toggleable',
                extend: 'colvis',
                text: 'Show/hide columns'
            },
            {
                className: 'btn-sm btn-dark',
                columns: '.link',
                extend: 'columnToggle',
                text: 'Show/hide links'
            }
        ]
    });
    initSearchForm();
    initWhatsThis();
});

function initWhatsThis() {
    $('.what_link').click(function (ev) {
        $('#what').toggle(500);
        ev.preventDefault();
    });
}

function addFieldsetCheckbox($fieldset, itemId, itemValue, itemLabel, title='') {
    $fieldset.append(
        '<label for="' + itemId + '"' + (title ? ' title="' + title + '"' : '') + '>' + itemLabel + '</label>' +
        '<input type="checkbox" name="' + itemId + '" id="' + itemId + '" value="' + itemValue + '">'
    );
}

function addMultiselectFilter(fieldsetSelector, dataColIdx, choice2labelFunc, isAnd = false) {
    var $fieldset = $(fieldsetSelector);
    var idPrefix = 'filter-' + safeId(fieldsetSelector) + '-';

    $.each(getColValues(dataColIdx), function (_, choice) {
        addFieldsetCheckbox($fieldset, idPrefix + safeId(choice), choice, choice2labelFunc(choice));
    });

    addFieldsetCheckbox($fieldset, idPrefix + 'unknown', '', '<i class="fas fa-question-circle"></i>', 'Include unknown');

    var $checkboxes = $fieldset.find('input');

    multiselectFilters[fieldsetSelector] = {
        'checkboxes': $checkboxes,
        'dataColIdx': dataColIdx,
        'selectedValues': []
    };

    var filter = multiselectFilters[fieldsetSelector];

    $checkboxes.checkboxradio({
        icon: false
    }).change(getOnFilterChangeFunction(filter));

    $.fn.dataTable.ext.search.push(getDataTableFilterFunction(filter, isAnd));
}

function getOnFilterChangeFunction(filter) {
    return function() {
        filter['selectedValues'] = filter['checkboxes']
            .filter(':checked')
            .map(function (_, node) { return node.value; })
            .get();

        $dataTable.draw();
    }
}

function getDataTableFilterFunction(filter, isAnd) {
    return function (_, data, _) {
        var selectedCount = filter['selectedValues'].length;

        if (selectedCount === 0) {
            return true;
        }

        var showUnknown = filter['selectedValues'].indexOf('') !== -1;

        if (showUnknown && data[filter['dataColIdx']].trim() === '') {
            return true;
        }

        var selectedNoUnknownCount = showUnknown ? selectedCount - 1 : selectedCount;
        var count = 0;

        data[filter['dataColIdx']].split(',').forEach(function(value, _, _) {
            if (filter['selectedValues'].indexOf(value.trim()) !== -1) {
                count++;
            }
        });

        return isAnd && count === selectedNoUnknownCount || !isAnd && count > 0;
    }
}

function initSearchForm() {
    addMultiselectFilter('#countriesFilter', COUNTRY_COL_IDX, function (countryCode) {
        return '<span class="flag-icon flag-icon-' + countryCode + '"></span>';
    });

    addMultiselectFilter('#typesFilter', TYPES_COL_IDX, function (type) {
        return type;
    });

    addMultiselectFilter('#featuresFilter', FEATURES_COL_IDX, function (feature) {
        return feature;
    }, true);
}

function getColValues(columnIndex) {
    return $dataTable
        .column(columnIndex)
        .data()
        .join(',')
        .split(',')
        .map(function (type, _, _) { return type.trim(); })
        .sort()
        .filter((value, index, theArray) => value && index === theArray.indexOf(value));
}

function safeId(input) {
    return input.replace(/[^a-zA-Z0-9]+/, '_')
}
