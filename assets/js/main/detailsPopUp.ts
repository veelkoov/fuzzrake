'use strict';

import * as Mustache from "mustache";
import * as Utils from "./utils";
import Artisan from "../class/Artisan";

const HTML_SIGN_UNKNOWN = '<i class="fas fa-question-circle" title="Unknown"></i>';

let detailsPopUpTpl: string;
let $detailsPopUp: JQuery<HTMLElement>;

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
        let miniaturesStr: string = render('{{#artisan.scritchMiniatureUrls}}{{.}}\n{{/artisan.scritchMiniatureUrls}}').trimRight();
        let photosStr: string = render('{{#artisan.scritchPhotoUrls}}{{.}}\n{{/artisan.scritchPhotoUrls}}').trimRight();

        let miniatures: string[] = miniaturesStr.split('\n');
        let photos: string[] = photosStr.split('\n');

        if (miniaturesStr.length === 0 || miniatures.length !== photos.length) {
            return '';
        }

        let result: string = '';
        for (let i: number = 0; i < miniatures.length; i++) {
            result += `<div><a href="${photos[i]}" target="_blank"><img src="${miniatures[i]}" alt="" /></a></div>`;
        }

        return `<div class="imgs-container">${result}</div>`;
    }
}

function populatePopUpWithData(artisan: Artisan): void {
    $detailsPopUp.html(Mustache.render(detailsPopUpTpl, {
        artisan: artisan,
        optional: optionalTplFunc,
        photos: photosTplFunc,
        optionalList: optionalListTplFunc,
    }, {}, ['((', '))']));

    Utils.updateUpdateRequestData('updateRequestFull', artisan);
}

function detailsPopUpShowCallback(event: any) {
    populatePopUpWithData($(event.relatedTarget).closest('tr').data('artisan'));
}

export function init(): (() => void)[] {
    return [
        () => {
            $detailsPopUp = $('#artisanDetailsModal');

            $detailsPopUp.find('a[data-href]').each((index: number, element: HTMLElement) => {
                /* Grep code for WORKAROUND_PLACEHOLDERS_CREATINT_FAKE_404S: data-href ---> href */
                element.setAttribute('href', element.getAttribute('data-href'));
                element.removeAttribute('data-href');
            });
            $detailsPopUp.on('show.bs.modal', detailsPopUpShowCallback);
        },
        () => {
            detailsPopUpTpl = $detailsPopUp.html();
            Mustache.parse(detailsPopUpTpl);
        },
    ];
}
