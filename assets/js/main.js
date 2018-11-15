'use strict';

import $ from 'jquery';
import Choices from "../3rd-party/Choices/public/assets/scripts/choices";

require('../3rd-party/flag-icon-css/css/flag-icon.css');
require('../3rd-party/Choices/public/assets/styles/choices.css');

var $dataTable;
var filters = {};

const NAME_COLUMN_IDX = 0;
const COUNTRIES_COLUMN_IDX = 1;
const STYLES_COLUMN_IDX = 2;
const FEATURES_COLUMN_IDX = 4;

const REFERRER_HTML = "If you're going to contact the studio/maker, <u>please let them know you found them here!</u>" +
    " This will help us all a lot. Thank you!";

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
        .append('<a class="btn btn-success btn-sm" href="./info.html#data-updates">Studio missing?</a>'); // FIXME: Use router
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
    initSelectFilter('#countriesFilter', COUNTRIES_COLUMN_IDX, true, false, countriesOnCreateTemplatesCallback);
    initSelectFilter('#stylesFilter', STYLES_COLUMN_IDX, false, false);
    initSelectFilter('#featuresFilter', FEATURES_COLUMN_IDX, false, true);
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

function initSelectFilter(selector, dataColumnIndex, forceOnMobile, isAnd, onCreateTemplatesCallback) {
    var useChoices = !isMobile() || forceOnMobile;

    var selectObj = useChoices ? new Choices(selector, {
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
        item: function item(classNames, data) {
            return template('<div class="' + classNames.item + ' ' + (data.highlighted ? classNames.highlightedState : classNames.itemSelectable) + '" data-item data-id="' + data.id + '" data-value="' + data.value + '" ' + (data.active ? 'aria-selected="true"' : '') + ' ' + (data.disabled ? 'aria-disabled="true"' : '') + '> ' + (data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label.replace(/^[A-Z]+ /, '') : data.label) + '</div>');
        },
        choice: function choice(classNames, data) {
            return template('<div class="' + classNames.item + ' ' + classNames.itemChoice + ' ' + (data.disabled ? classNames.itemDisabled : classNames.itemSelectable) + '" data-select-text="' + _this.config.itemSelectText + '" data-choice ' + (data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable') + ' data-id="' + data.id + '" data-value="' + data.value + '" ' + (data.groupId > 0 ? 'role="treeitem"' : 'role="option"') + '> ' + (data.label !== 'Show unknown' ? '<span class="flag-icon flag-icon-' + data.value + '"></span> ' + data.label : data.label) + '</div>');
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
            + ' <a href="./tracking.html">Learn more</a>'; // FIXME: Use router
    } else if (commissionsStatusData === '') {
        description = 'Commissions status is unknown. It should be tracked and updated automatically from this web page:'
            + ' <a href="' + cstUrl + '">' + cstUrl + '</a>, however the software failed to "understand"'
            + ' the status based on the page contents. Last time it tried on ' + cstLastCheck
            + ' UTC. <a href="./tracking.html">Learn more</a>'; // FIXME: Use router

        parsingFailed = true;
    } else {
        description = 'Commissions are <strong>' + commissionsStatus + '</strong>. Status is tracked and updated'
            + ' automatically from this web page: <a href="' + cstUrl + '">' + cstUrl + '</a>.'
            + ' Last time checked on ' + cstLastCheck + ' UTC.'
            + ' <a href="./tracking.html">Learn more</a>'; // FIXME: Use router
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

function isMobile() {
    // Get an up-to-date version from http://detectmobilebrowser.com/

    var ua = navigator.userAgent || navigator.vendor || window.opera;

    var result = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(ua) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(ua.substr(0, 4));

    isMobile = function() { return result; };

    return result;
}
