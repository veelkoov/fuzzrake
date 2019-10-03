'use strict';

import * as $ from "jquery";
import * as Mustache from "mustache";
import * as Utils from "./utils";
import Artisan from "./Artisan";

const HTML_SIGN_UNKNOWN = '<i class="fas fa-question-circle" title="Unknown"></i>';

let artisanDetailsModalTpl: string;
let $artisanDetailsModal: JQuery<HTMLElement>;

function formatLinks($links: any, completeness: number): string {
    $links.addClass('btn btn-light m-1');

    let updatesStr = completeness < Artisan.DATA_COMPLETE_LEVEL_GOOD ? ' and that their data could use some updates' : '';
    let $result = $('<div/>').append($links.length
        ? `<p class="small">If you're going to contact the studio/maker, <u>please let them know you found them here</u>${updatesStr}! This will help us all a lot. Thank you!</p>`
        : '<i class="fas fa-question-circle" title="None provided"></i>'
    ).append($links);

    return $result.html();
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
    }, {}, ['((', '))']));

    $('#artisanLinks').html(formatLinks(Utils.getLinks$(artisan), artisan.completeness));

    $('#makerId').attr('href', `#${artisan.makerId}`);

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
