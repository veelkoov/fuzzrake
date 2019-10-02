'use strict';

import * as $ from "jquery";
import * as Mustache from 'mustache';
import * as Utils from "./utils";
import Artisan from "./Artisan";

declare var TRACKING_URL: string;

const DATA_COMPLETE_PERFECT = 100;
const DATA_COMPLETE_GREAT = 90;
const DATA_COMPLETE_GOOD = 80;
const DATA_COMPLETE_OK = 60;

const HTML_SIGN_UNKNOWN = '<i class="fas fa-question-circle" title="Unknown"></i>';

let artisanDetailsModalTpl: string;
let $artisanDetailsModal: JQuery<HTMLElement>;

function formatLinks($links: any, completeness: number): string {
    $links.addClass('btn btn-light m-1');

    let updatesStr = completeness < DATA_COMPLETE_GOOD ? ' and that their data could use some updates' : '';
    let $result = $('<div/>').append($links.length
        ? `<p class="small">If you're going to contact the studio/maker, <u>please let them know you found them here</u>${updatesStr}! This will help us all a lot. Thank you!</p>`
        : '<i class="fas fa-question-circle" title="None provided"></i>'
    ).append($links);

    return $result.html();
}

function commissionStatusToString(commissionsStatus: boolean): string {
    return commissionsStatus === null ? 'unknown' : commissionsStatus ? 'open' : 'closed';
}

function formatCommissionsStatus(artisan: Artisan): string {
    let commissionsStatus = commissionStatusToString(artisan.commissionsStatus);

    if (artisan.cstUrl === '') {
        return `Commissions are <strong>${commissionsStatus}</strong>. Status is not automatically tracked and updated. <a href="${TRACKING_URL}">Learn more</a>`;
    } else if (artisan.commissionsStatus === null) {
        return `Commissions status is unknown. It should be tracked and updated automatically from this web page: <a href="${artisan.cstUrl}">${artisan.cstUrl}</a>, however the software failed to "understand" the status based on the page contents. Last time it tried on ${artisan.cstLastCheck} UTC. <a href="${TRACKING_URL}">Learn more</a>`;
    } else {
        return `Commissions are <strong>${commissionsStatus}</strong>. Status is tracked and updated automatically from this web page: <a href="${artisan.cstUrl}">${artisan.cstUrl}</a>. Last time checked on ${artisan.cstLastCheck} UTC. <a href="${TRACKING_URL}">Learn more</a>`;
    }
}

function getCompletenessComment(completeness: number): string {
    if (completeness >= DATA_COMPLETE_PERFECT) {
        return 'Awesome! <i class="fas fa-heart"></i>';
    } else if (completeness >= DATA_COMPLETE_GREAT) {
        return 'Great!'
    } else if (completeness >= DATA_COMPLETE_GOOD) {
        return 'Good job!'
    } else if (completeness >= DATA_COMPLETE_OK) {
        return 'Some updates might be helpful...';
    } else {
        return 'Yikes! :( Updates needed!'
    }
}

function fillDetailsModalHtml(artisan: Artisan): void {
    let updates = {
        '#artisanCompleteness': artisan.completeness.toString(),
        '#artisanLinks': formatLinks(Utils.getLinks$(artisan), artisan.completeness),
        '#artisanCompletenessComment': getCompletenessComment(artisan.completeness),
        '#artisanCommissionsStatus': formatCommissionsStatus(artisan),
    };

    for (let selector in updates) {
        $(selector).html(updates[selector]);
    }
}

function optionalTplFunc() {
    return function (text, render) {
        return render('{{ ' + text + ' }}') || HTML_SIGN_UNKNOWN;
    }
}

function optionalListTplFunc() {
    return function (text, render) {
        let rendered = render('{{# ' + text + ' }}<li>{{.}}</li>{{/ ' + text + ' }}');

        return rendered ? '<ul>' + rendered + '</ul>' : HTML_SIGN_UNKNOWN;
    }
}

function updateDetailsModalWithArtisanData(artisan: Artisan): void {
    $artisanDetailsModal.html(Mustache.render(artisanDetailsModalTpl, {
        artisan: artisan,
        optional: optionalTplFunc,
        optionalList: optionalListTplFunc,
    }, {}, ['[[', ']]']));

    fillDetailsModalHtml(artisan);

    $('#makerId').attr('href', `#${artisan.makerId}`);
    $('#statusParsingFailed').toggle(artisan.commissionsStatus === null);

    Utils.updateUpdateRequestData('updateRequestFull', artisan);

    Utils.makeLinksOpenNewTab('#artisanLinks a');
    Utils.makeLinksOpenNewTab('#artisanCommissionsStatus a');
}

export function init(): void {
    $artisanDetailsModal = $('#artisanDetailsModal');
    artisanDetailsModalTpl = $artisanDetailsModal.html();
    Mustache.parse(artisanDetailsModalTpl);

    $artisanDetailsModal.on('show.bs.modal', function (event: any) {
        updateDetailsModalWithArtisanData($(event.relatedTarget).closest('tr').data('artisan'));
    });

    Utils.makeLinksOpenNewTab('#updateRequestFull a');
}
