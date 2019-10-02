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

let artisanDetailsModalTpl: string;
let $artisanDetailsModal: JQuery<HTMLElement>;

function strongPrefixHtml(items: string, prefix: string): string {
    return items ? `<strong>${prefix}</strong>: ${items}` : '';
}

function formatSpecies(speciesDoes: string, speciesDoesnt: string): string {
    let doesHtml = strongPrefixHtml(speciesDoes, "Does");
    let doesntHtml = strongPrefixHtml(speciesDoesnt, "Doesn't");

    if (doesHtml !== '' && doesntHtml !== '') {
        return `${doesHtml}<br />${doesntHtml}`;
    } else if (doesHtml + doesntHtml !== '') {
        return `${doesHtml}${doesntHtml}`;
    } else {
        return '<i class="fas fa-question-circle"></i>';
    }
}

function formatPaymentPlans(paymentPlans: string): string {
    return paymentPlans || '<i class="fas fa-question-circle"></i>';
}

function formatLanguages(languages: string[]): string {
    return languages.join(', ') || '<i class="fas fa-question-circle"></i>';
}

function formatLinks($links: any, completeness: number): string {
    $links.addClass('btn btn-light m-1');

    let updatesStr = completeness < DATA_COMPLETE_GOOD ? ' and that their data could use some updates' : '';
    let $result = $('<div/>').append($links.length
        ? `<p class="small">If you're going to contact the studio/maker, <u>please let them know you found them here</u>${updatesStr}! This will help us all a lot. Thank you!</p>`
        : '<i class="fas fa-question-circle" title="None provided"></i>'
    ).append($links);

    return $result.html();
}

function htmlListFromArrays(list: String[], other: String[] = []): string {
    let listLis = list.length ? `<li>${list.join('</li><li>')}</li>` : '';
    let otherLis = other.length ? `<li>Other: ${other.join('; ')}</li>` : '';

    return listLis + otherLis ? `<ul>${listLis}${otherLis}</ul>` : '<i class="fas fa-question-circle"></i>';
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

function formatMakerId(artisan: Artisan): string {
    return artisan.makerId ? '<i class="fas fa-link"></i> ' + artisan.makerId : '';
}

function formatName(artisan: Artisan): string {
    return artisan.name + Utils.countryFlagHtml(artisan.country);
}

function fillDetailsModalHtml(artisan: Artisan): void {
    let updates = {
        '#artisanProductionModel': htmlListFromArrays(artisan.productionModels),
        '#artisanStyles': htmlListFromArrays(artisan.styles, artisan.otherStyles),
        '#artisanTypes': htmlListFromArrays(artisan.orderTypes, artisan.otherOrderTypes),
        '#artisanFeatures': htmlListFromArrays(artisan.features, artisan.otherFeatures),

        '#artisanSpecies': formatSpecies(artisan.speciesDoes, artisan.speciesDoesnt),
        '#artisanPaymentPlans': formatPaymentPlans(artisan.paymentPlans),
        '#artisanLanguages': formatLanguages(artisan.languages),
        '#artisanCompleteness': artisan.completeness.toString(),
        '#artisanLinks': formatLinks(Utils.getLinks$(artisan), artisan.completeness),
        '#artisanCompletenessComment': getCompletenessComment(artisan.completeness),
        '#artisanCommissionsStatus': formatCommissionsStatus(artisan),
    };

    for (let selector in updates) {
        $(selector).html(updates[selector]);
    }
}

function updateDetailsModalWithArtisanData(artisan: Artisan): void {
    $artisanDetailsModal.html(Mustache.render(artisanDetailsModalTpl, {
        artisan: artisan,
        optional: function () {
            return function (text, render) {
                let rendered = render(text);

                return rendered || '<i class="fas fa-question-circle" title="Unknown"></i>';
            }
        }
    }));

    fillDetailsModalHtml(artisan);

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
