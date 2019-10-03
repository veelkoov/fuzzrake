'use strict';

import * as $ from "jquery";
import * as Mustache from "mustache";
import * as Utils from "./utils";
import Artisan from "./Artisan";

const HTML_SIGN_UNKNOWN = '<i class="fas fa-question-circle" title="Unknown"></i>';

let artisanDetailsModalTpl: string;
let $artisanDetailsModal: JQuery<HTMLElement>;

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

    Utils.updateUpdateRequestData('updateRequestFull', artisan);
}

export function init(): void {
    $artisanDetailsModal = $('#artisanDetailsModal');
    artisanDetailsModalTpl = $artisanDetailsModal.html();
    Mustache.parse(artisanDetailsModalTpl);

    $artisanDetailsModal.on('show.bs.modal', function (event: any) {
        updateDetailsModalWithArtisanData($(event.relatedTarget).closest('tr').data('artisan'));
    });
}
