import $ from "jquery";
import * as Utils from "./utils";
import * as Consts from "./consts";

function formatShortInfo(state, city, since, formerly) {
    since = since || '<i class="fas fa-question-circle" title="How long?"></i>';
    formerly = formerly ? '<br />Formerly ' + formerly : '';

    let location = [state, city].filter(function (i) {
        return i;
    }).join(', ') || '<i class="fas fa-question-circle" title="Where are you?"></i>';

    return 'Based in ' + location + ', crafting since ' + since + formerly;
}

function formatLinks(links) {
    let linksHtml = '';

    links.each(function (_, link) {
        let $link = $(link).clone();
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
        ? '<p class="small px-1">' + Consts.REFERRER_HTML + '</p>' + linksHtml
        : '<i class="fas fa-question-circle" title="None provided"></i>';
}

function htmlListFromCommaSeparated(list, other) {
    let listLis = list !== '' ? '<li>' + list.split(', ').join('</li><li>') + '</li>' : '';
    let otherLis = other !== '' ? '<li>Other: ' + other + '</li>' : '';

    return listLis + otherLis ? '<ul>' + listLis + otherLis + '</ul>' : '<i class="fas fa-question-circle"></i>';
}

function updateCommissionsStatusFromArtisanRowData(commissionsStatusData, cstLastCheck, cstUrl) {
    let commissionsStatus = commissionsStatusData === '' ? 'unknown' : commissionsStatusData ? 'open' : 'closed';
    let description;
    let parsingFailed = false;

    if (cstUrl === '') {
        description = 'Commissions are <strong>' + commissionsStatus + '</strong>.'
            + ' Status is not automatically tracked and updated.'
            + ' <a href="' + TRACKING_URL + '">Learn more</a>';
    } else if (commissionsStatusData === '') {
        description = 'Commissions status is unknown. It should be tracked and updated automatically from this web page:'
            + ' <a href="' + cstUrl + '">' + cstUrl + '</a>, however the software failed to "understand"'
            + ' the status based on the page contents. Last time it tried on ' + cstLastCheck
            + ' UTC. <a href="' + TRACKING_URL + '">Learn more</a>';

        parsingFailed = true;
    } else {
        description = 'Commissions are <strong>' + commissionsStatus + '</strong>. Status is tracked and updated'
            + ' automatically from this web page: <a href="' + cstUrl + '">' + cstUrl + '</a>.'
            + ' Last time checked on ' + cstLastCheck + ' UTC.'
            + ' <a href="' + TRACKING_URL + '">Learn more</a>';
    }

    $('#artisanCommissionsStatus').html(description);
    $('#statusParsingFailed').toggle(parsingFailed);
}

function updateDetailsModalWithRowData($row) {
    $('#artisanName').html($row.children().eq(Consts.NAME_COLUMN_IDX).html());
    $('#artisanShortInfo').html(formatShortInfo($row.data('state'), $row.data('city'), $row.data('since'), $row.data('formerly')));
    $('#artisanFeatures').html(htmlListFromCommaSeparated($row.data('features'), $row.data('other-features')));
    $('#artisanTypes').html(htmlListFromCommaSeparated($row.data('types'), $row.data('other-types')));
    $('#artisanStyles').html(htmlListFromCommaSeparated($row.data('styles'), $row.data('other-styles')));
    $('#artisanLinks').html(formatLinks($row.find('div.artisan-links div.dropdown-menu a:not(.request-update)')));
    $('#artisanRequestUpdate').attr('href', $row.find('div.artisan-links div.dropdown-menu a.request-update').attr('href'));
    $('#artisanIntro').html($row.data('intro')).toggle($row.data('intro') !== '');

    updateCommissionsStatusFromArtisanRowData($row.data('commissions-status'), $row.data('cst-last-check'), $row.data('cst-url'));
    Utils.updateUpdateRequestData('updateRequestFull', $row);

    Utils.makeLinksOpenNewTab('#artisanLinks a');
    Utils.makeLinksOpenNewTab('#artisanCommissionsStatus a');
}

export function init() {
    $('#artisanDetailsModal').on('show.bs.modal', function (event) {
        updateDetailsModalWithRowData($(event.relatedTarget).closest('tr'));
    });

    Utils.makeLinksOpenNewTab('#updateRequestFull a');
}
