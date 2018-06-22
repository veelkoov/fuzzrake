var $dataTable;
var $countryCheckboxes;
var selectedCountries = [];
var COUNTRY_COL_IDX = 1;

$(document).ready(function () {
    $dataTable = $('#artisans').DataTable({"paging": false});
    initSearchForm();
    initWhatsThis();
});

function applyFilters() {
    selectedCountries = $countryCheckboxes
        .filter(':checked')
        .map(function (_, node) { return node.value; })
        .get();

    $dataTable.draw();
}

function initSearchForm() {
    var $countriesFilter = $('#countriesFilter')

    $.each(getCountryCodes(), function (idx, countryCode) {
        $countriesFilter.append(
            '<label for="filter-' + countryCode + '">' +
            '   <span class="flag-icon flag-icon-' + countryCode + '"></span>' +
            '</label>' +
            '<input type="checkbox" name="filter-' + countryCode + '" id="filter-' + countryCode + '" value="' + countryCode + '">'
        );
    });

    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            return selectedCountries.length === 0 || selectedCountries.indexOf(data[COUNTRY_COL_IDX]) !== -1;
        }
    );

    $countryCheckboxes = $countriesFilter.find('input');

    $countryCheckboxes.checkboxradio({
        icon: false
    }).change(function () {
        applyFilters();
    });
}

function getCountryCodes() {
    return $dataTable
        .column(COUNTRY_COL_IDX)
        .data()
        .sort()
        .unique()
        .filter((value, _) => value);
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