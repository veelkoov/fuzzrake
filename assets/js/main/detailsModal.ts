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

function photosTplFunc() {
    return function (text, render) {
        let miniaturesStr: string = render('{{#artisan.scritchMiniatureUrls}}{{.}}\n{{/artisan.scritchMiniatureUrls}}');
        let photosStr: string = render('{{#artisan.scritchPhotoUrls}}{{.}}\n{{/artisan.scritchPhotoUrls}}');

        let miniatures: string[] = miniaturesStr.trimRight().split('\n');
        let photos: string[] = photosStr.trimRight().split('\n');

        if (miniatures.length === 0 || miniatures.length !== photos.length) {
            return '';
        }

        let result: string = '';
        for (let i: number = 0; i < miniatures.length; i++) {
            // result += `<div><img src="${miniatures[i]}" alt="" /></div>`;
            result += `<div><a href="${photos[i]}" target="_blank"><img src="${miniatures[i]}" alt="" /></a></div>`;
        }

        return `<div class="imgs-container">${result}</div>`;
    }
}

function updateDetailsModalWithArtisanData(artisan: Artisan): void {
    $artisanDetailsModal.html(Mustache.render(artisanDetailsModalTpl, {
        artisan: artisan,
        optional: optionalTplFunc,
        photos: photosTplFunc,
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
