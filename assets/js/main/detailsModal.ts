import * as $ from "jquery";
import * as Utils from "./utils";
import * as Consts from "./consts";
import Artisan from "./Artisan";

declare var TRACKING_URL: string;

function formatShortInfo(state, city, since, formerly) {
    since = since || '<i class="fas fa-question-circle" title="How long?"></i>';
    formerly = formerly ? `<br />Formerly ${formerly}` : '';

    let location = [state, city].filter(i => i).join(', ') || '<i class="fas fa-question-circle" title="Where are you?"></i>';

    return `Based in ${location}, crafting since ${since}${formerly}`;
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
        ? `<p class="small px-1">${Consts.REFERRER_HTML}</p>${linksHtml}`
        : '<i class="fas fa-question-circle" title="None provided"></i>';
}

function htmlListFromArrays(list: String[], other: String[]) {
    let listLis = list.length ? `<li>${list.join('</li><li>')}</li>` : '';
    let otherLis = other.length ? `<li>Other: ${other.join('; ')}</li>` : '';

    return listLis + otherLis ? `<ul>${listLis}${otherLis}</ul>` : '<i class="fas fa-question-circle"></i>';
}

function updateCommissionsStatusFromArtisanRowData(commissionsStatusData, cstLastCheck, cstUrl) {
    let commissionsStatus = commissionsStatusData === '' ? 'unknown' : commissionsStatusData ? 'open' : 'closed';
    let description;
    let parsingFailed = false;

    if (cstUrl === '') {
        description = `Commissions are <strong>${commissionsStatus}</strong>. Status is not automatically
                        tracked and updated. <a href="${TRACKING_URL}">Learn more</a>`;
    } else if (commissionsStatusData === '') {
        description = `Commissions status is unknown. It should be tracked and updated automatically from
                        this web page: <a href="${cstUrl}">${cstUrl}</a>, however the software failed to
                        "understand" the status based on the page contents. Last time it tried
                        on ${cstLastCheck} UTC. <a href="${TRACKING_URL}">Learn more</a>`;

        parsingFailed = true;
    } else {
        description = `Commissions are <strong>${commissionsStatus}</strong>. Status is tracked and updated
                        automatically from this web page: <a href="${cstUrl}">${cstUrl}</a>. Last time checked
                        on ${cstLastCheck} UTC. <a href="${TRACKING_URL}">Learn more</a>`;
    }

    $('#artisanCommissionsStatus').html(description);
    $('#statusParsingFailed').toggle(parsingFailed);
}

function updateDetailsModalWithRowData(artisan: Artisan) {
    $('#artisanName').html(artisan.name + Utils.countryFlagHtml(artisan.country));
    $('#artisanShortInfo').html(formatShortInfo(artisan.state, artisan.city, artisan.since, artisan.formerly));
    $('#artisanStyles').html(htmlListFromArrays(artisan.styles, artisan.otherStyles));
    $('#artisanTypes').html(htmlListFromArrays(artisan.types, artisan.otherTypes));
    $('#artisanFeatures').html(htmlListFromArrays(artisan.features, artisan.otherFeatures));
    // $('#artisanLinks').html(formatLinks($row.find('div.artisan-links div.dropdown-menu a:not(.request-update)')));
    // $('#artisanRequestUpdate').attr('href', $row.find('div.artisan-links div.dropdown-menu a.request-update').attr('href'));
    $('#artisanIntro').html(artisan.intro).toggle(artisan.intro !== '');

    updateCommissionsStatusFromArtisanRowData(artisan.areCommissionsOpen, artisan.commissionsQuotesLastCheck,
        artisan.commisionsQuotesCheckUrl);
    // Utils.updateUpdateRequestData('updateRequestFull', $row);

    Utils.makeLinksOpenNewTab('#artisanLinks a');
    Utils.makeLinksOpenNewTab('#artisanCommissionsStatus a');
}

export function init() {
    $('#artisanDetailsModal').on('show.bs.modal', event => {
        updateDetailsModalWithRowData($(event.relatedTarget).closest('tr').data('artisan'));
    });

    Utils.makeLinksOpenNewTab('#updateRequestFull a');
}
