var $dataTable;
var COUNTRY_COL_IDX = 1;
var TYPES_COL_IDX = 2;
var multiselectFilters = {};

$(document).ready(function () {
    $dataTable = $('#artisans').DataTable({"paging": false});
    initSearchForm();
    initWhatsThis();
});

function addMultiselectFilter(fieldsetSelector, choices, choice2idFunc, choice2labelFunc, dataColIdx) {
    var $fieldset = $(fieldsetSelector);

    $.each(choices, function (idx, choice) {
        var choiceId = choice2idFunc(choice);
        var choiceLabel = choice2labelFunc(choice);

        $fieldset.append(
            '<label for="filter-' + choiceId + '">' + choiceLabel + '</label>' +
            '<input type="checkbox" name="filter-' + choiceId + '" id="filter-' + choiceId + '" value="' + choice + '">'
        );
    });

    var $checkboxes = $fieldset.find('input');

    multiselectFilters[fieldsetSelector] = {
        'checkboxes': $checkboxes,
        'dataColIdx': dataColIdx,
        'selectedValues': []
    };

    var filter = multiselectFilters[fieldsetSelector];

    $checkboxes.checkboxradio({
        icon: false
    }).change(function () {
        filter['selectedValues'] = filter['checkboxes']
            .filter(':checked')
            .map(function (_, node) { return node.value; })
            .get();

        $dataTable.draw();
    });

    $.fn.dataTable.ext.search.push(function (_, data, _) {
        if (filter['selectedValues'].length === 0) {
            return true;
        }

        var result = false;

        data[filter['dataColIdx']].split(',').forEach(function(value, index, theArray) {
            if (filter['selectedValues'].indexOf(value.trim()) !== -1) {
                result = true;
            }
        });

        return result;
    });
}

function initSearchForm() {
    addMultiselectFilter('#countriesFilter', getCountryCodes(), function (countryCode) {
        return 'country-' + countryCode;
    }, function (countryCode) {
        return '<span class="flag-icon flag-icon-' + countryCode + '"></span>';
    }, COUNTRY_COL_IDX);

    addMultiselectFilter('#typesFilter', getTypes(), function (type) {
        return 'type-' + type.toLowerCase().replace(' ', '-');
    }, function (type) {
        return type;
    }, TYPES_COL_IDX);
}

function getCountryCodes() {
    return $dataTable
        .column(COUNTRY_COL_IDX)
        .data()
        .sort()
        .unique()
        .filter((value, _) => value);
}

function getTypes() {
    return $dataTable
        .column(TYPES_COL_IDX)
        .data()
        .join(',')
        .split(',')
        .map(function (type, _, _) { return type.trim(); })
        .sort()
        .filter(function (value, index, theArray) {
            return value && index === theArray.indexOf(value);
        });
}

function initWhatsThis() {
    $('.what_link').click(function (ev) {
        $('#what').toggle(500);
        ev.preventDefault();
    });
}

function getCountryId(countryCode) {
    return countryCode ? 'country-' + countryCode.toLowerCase() : '';
}