'use strict';

import $ from 'jquery';
import initDataTable from './datatable';
import * as consts from './consts';

require('../../3rd-party/flag-icon-css/css/flag-icon.css');

$(document).ready(function () {
    initDataTable();
    initDetailsModal();
    initRequestUpdateModal();
    addReferrerRequestTooltip();
    makeLinksOpenNewTab('#artisans a:not(.request-update)');
});

function makeLinksOpenNewTab(linkSelector) {
    $(linkSelector).click(function (evt) {
        evt.preventDefault();
        window.open(this.href);
    });
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

function addReferrerRequestTooltip() {
    $('div.artisan-links')
        .attr('title', consts.REFERRER_HTML)
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
    $('#artisanName').html($row.children().eq(consts.NAME_COLUMN_IDX).html());
    $('#artisanShortInfo').html(formatShortInfo($row.data('state'), $row.data('city'), $row.data('since'), $row.data('formerly')));
    $('#artisanFeatures').html(htmlListFromCommaSeparated($row.data('features'), $row.data('other-features')));
    $('#artisanTypes').html(htmlListFromCommaSeparated($row.data('types'), $row.data('other-types')));
    $('#artisanStyles').html(htmlListFromCommaSeparated($row.data('styles'), $row.data('other-styles')));
    $('#artisanLinks').html(formatLinks($row.find('div.artisan-links div.dropdown-menu a:not(.request-update)')));
    $('#artisanRequestUpdate').attr('href', $row.find('div.artisan-links div.dropdown-menu a.request-update').attr('href'));
    $('#artisanIntro').html($row.data('intro')).toggle($row.data('intro') !== '');

    updateCommissionsStatusFromArtisanRowData($row.data('commissions-status'), $row.data('cst-last-check'), $row.data('cst-url'));
    updateUpdateRequestData('updateRequestFull', $row);

    makeLinksOpenNewTab('#artisanLinks a');
    makeLinksOpenNewTab('#artisanCommissionsStatus a');
}

function updateUpdateRequestData(divId, $row) {
    $('#' + divId + ' .twitterUrl').attr('href', 'https://twitter.com/intent/tweet?original_referer=http%3A%2F%2Fgetfursu.it%2F&ref_src=twsrc%5Etfw&screen_name=Veelkoov&text=Fursuit%20maker%20update%20request%3A%20' + encodeURIComponent($row.data('name')) + '%20(please%20describe%20details)&tw_p=tweetbutton');

    $('#' + divId + ' .googleFromUrl').attr('href', 'https://docs.google.com/forms/d/e/1FAIpQLSd72ex2FgHbJvkPRiADON0oCJx75JzQQCOLEQIGaSt3DSy2-Q/viewform?usp=pp_url&entry.1289735951=' + encodeURIComponent($row.data('name')));
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
        ? '<p class="small px-1">' + consts.REFERRER_HTML + '</p>' + linksHtml
        : '<i class="fas fa-question-circle" title="None provided"></i>';
}
