var $dataTable;
var COUNTRY_COL_IDX = 1;
var TYPES_COL_IDX = 2;
var multiselectFilters = {};

$(document).ready(function () {
    $dataTable = $('#artisans').DataTable({"paging": false});
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

function addMultiselectFilter(fieldsetSelector, dataColIdx, choice2labelFunc) {
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

    $.fn.dataTable.ext.search.push(getDataTableFilterFunction(filter));
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

function getDataTableFilterFunction(filter) {
    return function (_, data, _) {
        if (filter['selectedValues'].length === 0) {
            return true;
        }

        if (data[filter['dataColIdx']].trim() === '' && filter['selectedValues'].indexOf('') !== -1) {
            return true;
        }

        var result = false;

        data[filter['dataColIdx']].split(',').forEach(function(value, _, _) {
            if (filter['selectedValues'].indexOf(value.trim()) !== -1) {
                result = true;
            }
        });

        return result;
    }
}

function initSearchForm() {
    addMultiselectFilter('#countriesFilter', COUNTRY_COL_IDX, function (countryCode) {
        return '<span class="flag-icon flag-icon-' + countryCode + '"></span>';
    });

    addMultiselectFilter('#typesFilter', TYPES_COL_IDX, function (type) {
        return type;
    });
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
