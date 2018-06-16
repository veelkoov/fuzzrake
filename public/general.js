var $dataTable;
var $countryCheckboxes;

$(document).ready(function () {
    initDataTable();
    initWhatsThis();
});

function initDataTable() {
    $.ajax({
        url: 'data.json',
        success: function (artisansData) {
            insertDataTableRows(artisansData);
            initSearchForm(artisansData);

            $dataTable = $('#artisans').DataTable({"paging": false});
        }
    });
}

function applyFilters() {
    var countryRegexp = $countryCheckboxes
        .filter(':checked')
        .map(function (idx, el) { return getCountryId(el.value); })
        .get()
        .join('|');

    $dataTable.search(countryRegexp, true, false).draw();
}

function initSearchForm(artisansData) {
    var countryCodes = getCountryCodes(artisansData);
    var $countriesFilter = $('#countriesFilter')

    $.each(countryCodes, function (idx, countryCode) {
        $countriesFilter.append(
            '<label for="filter-' + countryCode + '">' +
            '   <span class="flag-icon flag-icon-' + countryCode + '"></span>' +
            '</label>' +
            '<input type="checkbox" name="filter-' + countryCode + '" id="filter-' + countryCode + '" value="' + countryCode + '">'
        );
    });

    $countryCheckboxes = $countriesFilter.find('input');

    $countryCheckboxes.checkboxradio({
        icon: false
    }).change(function () {
        applyFilters();
    })
}

function getCountryCodes(artisansData) {
    return $.map(artisansData, function (val, i) { return val.country.toLowerCase(); })
        .sort()
        .filter((v, i, a) => a.indexOf(v) == i && v);
}

function initWhatsThis() {
    $('.what_link').click(function (ev) {
        $('#what h1').html(function () {
            var hour = new Date().getHours();
            return 'Good ' + (hour >= 4 && hour < 12 ? 'Morning' : hour >= 12 && hour < 20 ? 'Afternoon' : 'Evening') + '!';
        });

        $('#what').toggle(500);
        ev.preventDefault();
    });
}

function insertDataTableRows(artisansData) {
    var $artisans = $('#artisans tbody');

    $.each(artisansData, function (idx, artisan) {
        $artisans.append(getDataTableRow(artisan));
    });
}

function getDataTableRow(artisan) {
    return '<tr>' +
        '<td class="text-left">' + artisan.name +
        (artisan.country ? ' <span class="flag-icon flag-icon-' + artisan.country.toLowerCase() + '"></span>' : '') +
        '</td>' +
        '<td class="hidden_data">' + getCountryId(artisan.country) + '</td>' +
        '<td>' + getLinkCode(artisan.websiteUrl, '<i class="fas fa-link"></i>') + '</td>' +
        '<td>' + getLinkCode(artisan.furAffinityUrl, '<img src="FurAffinity.svg"/>') + '</td>' +
        '<td>' + getLinkCode(artisan.deviantArtUrl, '<i class="fab fa-deviantart"></i>') + '</td>' +
        '<td>' + getLinkCode(artisan.twitterUrl, '<i class="fab fa-twitter"></i>') + '</td>' +
        '<td>' + getLinkCode(artisan.facebookUrl, '<i class="fab fa-facebook"></i>') + '</td>' +
        '<td>' + getLinkCode(artisan.tumblrUrl, '<i class="fab fa-tumblr"></i>') + '</td>' +
        '</tr>';
}

function getLinkCode(href, contents) {
    if (!href) {
        return '';
    }

    return '<a href="' + href + '">' + contents + '</a>';
}

function getCountryId(countryCode) {
    return countryCode ? 'country-' + countryCode.toLowerCase() : '';
}