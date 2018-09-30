'use strict';

var $dataTable;
var filters = {};

var NAME_COLUMN_IDX = 0;
var COUNTRIES_COLUMN_IDX = 1;
var STYLES_COLUMN_IDX = 2;
var FEATURES_COLUMN_IDX = 4;

var REFERRER_HTML = "If you're going to contact the studio/maker, <u>please let them know you found them here!</u>" +
    " This will help us all a lot. Thank you!";
var DATA_UPDATES_INFO_URL = "{{ path('info') }}#data-updates";

$(document).ready(function () {
    initDataTable();
    initDetailsModal();
    initRequestUpdateModal();
    initSearchForm();
    addReferrerRequestTooltip();
});

function makeLinksOpenNewTab(linkSelector) {
    $(linkSelector).click(function (evt) {
        evt.preventDefault();
        window.open(this.href);
    });
}

function initDataTable() {
    $dataTable = $('#artisans').DataTable({
        dom:
            "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        paging: false,
        autoWidth: false,
        columnDefs: [{ targets: 'no-sorting', orderable: false }, { targets: 'default-hidden', visible: false }],
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
        .append('<a class="btn btn-success btn-sm" href="' + DATA_UPDATES_INFO_URL + '">Studio missing?</a>');
    makeLinksOpenNewTab('#artisans a:not(.request-update)');
}

function initDetailsModal() {
    $('#artisanDetailsModal').on('show.bs.modal', function (event) {
        updateDetailsModalWithRowData($(event.relatedTarget).closest('tr'));
    });

    makeLinksOpenNewTab('#updateRequestFull a');
}

function initRequestUpdateModal() {
    $('#updateRequestModal').on('show.bs.modal', function (event) {
        updateRequestUpdateModalWithRowData($(event.relatedTarget).closest('tr'));
    });

    makeLinksOpenNewTab('#updateRequestModal a');
}

function initSearchForm() {
    addChoiceWidget('#countriesFilter', COUNTRIES_COLUMN_IDX, false, countriesOnCreateTemplatesCallback);
    addChoiceWidget('#stylesFilter', STYLES_COLUMN_IDX, false);
    addChoiceWidget('#featuresFilter', FEATURES_COLUMN_IDX, true);
}

function addReferrerRequestTooltip() {
    $('div.artisan-links')
        .attr('title', REFERRER_HTML)
        .data('placement', 'top')
        .data('boundary', 'window')
        .data('html', true)
        .data('fallbackPlacement', [])
        .tooltip();
}

function updateRequestUpdateModalWithRowData($row) {
    $('#artisanNameUR').html($row.data('name'));

    updateUpdateRequestData('updateRequestSingle', $row);
}

function updateDetailsModalWithRowData($row) {
    $('#artisanName').html($row.children().eq(NAME_COLUMN_IDX).html());
    $('#artisanShortInfo').html(formatShortInfo($row.data('state'), $row.data('city'), $row.data('since'), $row.data('formerly')));
    $('#artisanFeatures').html(htmlListFromCommaSeparated($row.data('features'), $row.data('other-features')));
    $('#artisanTypes').html(htmlListFromCommaSeparated($row.data('types'), $row.data('other-types')));
    $('#artisanStyles').html(htmlListFromCommaSeparated($row.data('styles'), $row.data('other-styles')));
    $('#artisanLinks').html(formatLinks($row.find('div.artisan-links div.dropdown-menu a:not(.request-update)')));
    $('#artisanRequestUpdate').attr('href', $row.find('div.artisan-links div.dropdown-menu a.request-update').attr('href'));
    $('#artisanIntro').html($row.data('intro')).toggle($row.data('intro') !== '');

    updateCommissionsStatusFromArtisanRowData($row.data('commissions-status'), $row.data('cst-last-check'), $row.data('cst-url'))
    updateUpdateRequestData('updateRequestFull', $row);

    makeLinksOpenNewTab('#artisanLinks a');
    makeLinksOpenNewTab('#artisanCommissionsStatus a');
}

function updateUpdateRequestData(divId, $row) {
    $('#' + divId + ' .twitterUrl').attr('href', 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent($row.data('name')) + '%20(please%20describe%20details)&tw_p=tweetbutton');

    $('#' + divId + ' .googleFromUrl').attr('href', 'https://docs.google.com/forms/d/e/1FAIpQLSd72ex2FgHbJvkPRiADON0oCJx75JzQQCOLEQIGaSt3DSy2-Q/viewform?usp=pp_url&entry.1289735951=' + encodeURIComponent($row.data('name')));
}

function htmlListFromCommaSeparated(list, other) {
    var listLis = list !== '' ? '<li>' + list.split(', ').join('</li><li>') + '</li>' : '';
    var otherLis = other !== '' ? '<li>Other: ' + other + '</li>' : '';

    return listLis + otherLis ? '<ul>' + listLis + otherLis + '</ul>' : '<i class="fas fa-question-circle"></i>';
}

function addChoiceWidget(selector, dataColumnIndex, isAnd, onCreateTemplatesCallback) {
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
    $.each(filters, function (_, filter) {
        filter['selectedValues'] = filter['$select'].val();
    });

    $dataTable.draw();
}

function getDataTableFilterFunction(filter, isAnd) {
    return function (_, data, __) {
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

        data[filter['dataColumnIndex']].split(',').forEach(function (value, _, __) {
            if (filter['selectedValues'].indexOf(value.trim()) !== -1) {
                count++;
            }
        });

        return count > 0 && (!isAnd || count === selectedNoUnknownCount);
    };
}

function countriesOnCreateTemplatesCallback(template) {
    var _this = this;
    var classNames = this.config.classNames;

    return {
        item: function item(data) {
            return template('<div class="' + classNames.item + ' ' + (data.highlighted ? classNames.highlightedState : '') + ' ' + (!data.disabled ? classNames.itemSelectable : '') + '" data-item data-id="' + data.id + '" data-value="' + data.value + '" ' + (data.active ? 'aria-selected="true"' : '') + ' ' + (data.disabled ? 'aria-disabled="true"' : '') + ' data-deletable>' + (data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label.replace(/^[A-Z]+ /, '') : data.label) + '<button class="' + classNames.button + '" data-button>Remove item</button></div>');
        },
        choice: function choice(data) {
            return template('<div class="' + classNames.item + ' ' + classNames.itemChoice + ' ' + (data.disabled ? classNames.itemDisabled : classNames.itemSelectable) + '" data-select-text="' + _this.config.itemSelectText + '" data-choice ' + (data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable') + ' data-id="' + data.id + '" data-value="' + data.value + '" ' + (data.groupId > 0 ? 'role="treeitem"' : 'role="option"') + '>' + (data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label : data.label) + '</div>');
        }
    };
}

function updateCommissionsStatusFromArtisanRowData(commissionsStatusData, cstLastCheck, cstUrl) {
    var commissionsStatus = commissionsStatusData === '' ? 'unknown' : commissionsStatusData ? 'open' : 'closed';
    var description;
    var parsingFailed = false;

    if (cstUrl === '') {
        description = 'Commissions are <strong>' + commissionsStatus + '</strong>.'
            + ' Status is not automatically tracked and updated.'
            + ' <a href="./info.html#commissions-status-tracking">Learn more</a>';
    } else if (commissionsStatusData === '') {
        description= 'Commissions status is unknown. It should be tracked and updated automatically from this web page:'
            + ' <a href="' + cstUrl + '">' + cstUrl + '</a>, however the software failed to "understand"'
            + ' the status based on the page contents. Last time it tried on ' + cstLastCheck
            + ' UTC. <a href="./info.html#commissions-status-tracking">Learn more</a>';

        parsingFailed = true;
    } else {
        description = 'Commissions are <strong>' + commissionsStatus + '</strong>. Status is tracked and updated'
            + ' automatically from this web page: <a href="' + cstUrl + '">' + cstUrl + '</a>.'
            + ' Last time checked on ' + cstLastCheck + ' UTC.'
            + ' <a href="./info.html#commissions-status-tracking">Learn more</a>';
    }

    $('#artisanCommissionsStatus').html(description);
    $('#statusParsingFailed').toggle(parsingFailed);
}

function formatShortInfo(state, city, since, formerly) {
    since = since || '<i class="fas fa-question-circle" title="How long?"></i>';
    formerly = formerly ? '<br />Formerly ' + formerly : '';

    var location = [state, city].filter(function (i) {
        return i;
    }).join(', ') || '<i class="fas fa-question-circle" title="Where are you?"></i>';

    return 'Based in ' + location + ', crafting since ' + since + formerly;
}

function formatLinks(links) {
    var linksHtml = '';

    links.each(function (_, link) {
        var $link = $(link).clone();
        $link
            .removeClass('dropdown-item')
            .addClass('btn btn-light m-1')
            .html(
                $link.html() + '<span class="d-none d-md-inline">: <span class="url">' +
                $link.attr('href').replace(/^https?:\/\/|\/$/g, '') + '</span></span>'
            );
        linksHtml += $link[0].outerHTML;
    });

    return linksHtml
        ? '<p class="small px-1">' + REFERRER_HTML + '</p>' + linksHtml
        : '<i class="fas fa-question-circle" title="None provided"></i>';
}
