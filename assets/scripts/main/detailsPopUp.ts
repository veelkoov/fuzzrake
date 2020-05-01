import * as Handlebars from "handlebars";
import * as Utils from "./utils";
import Artisan from "../class/Artisan";

const HTML_SIGN_UNKNOWN = new Handlebars.SafeString('<i class="fas fa-question-circle" title="Unknown"></i>');
const safeStr = Handlebars.Utils.escapeExpression;

let detailsPopUpTpl: HandlebarsTemplateDelegate;
let $detailsPopUp: JQuery<HTMLElement>;

function optionalTplFunc(element: string|string[]|Set<string>): string|object {
    if (element instanceof Set) {
        element = Array.from(element);
    }

    if (element instanceof Array) {
        element = element.join(', ');
    }

    return element !== '' ? element : HTML_SIGN_UNKNOWN;
}

function commaSeparatedTplFunc(elements: string[]): string {
    return elements.join(', ');
}

function optionalListTplFunc(list: string[]|Set<string>): string|object {
    if (list instanceof Set) {
        list = Array.from(list);
    }

    let rendered = list.map(function (value: string): string {
        return `<li>${safeStr(value)}</li>`;
    }).join('');

    return rendered ? new Handlebars.SafeString(`<ul>${rendered}</ul>`) : HTML_SIGN_UNKNOWN;
}

function photosTplFunc(miniatures: string[], photos: string[]): string|object {
    if (miniatures.length === 0 || miniatures.length !== photos.length) {
        return '';
    }

    let result: string = '';

    for (let i: number = 0; i < miniatures.length; i++) {
        result += `<div><a href="${safeStr(photos[i])}" target="_blank"><img src="${safeStr(miniatures[i])}" alt="" /></a></div>`;
    }

    return new Handlebars.SafeString(`<div class="imgs-container">${result}</div>`);
}

function populatePopUpWithData(artisan: Artisan): void {
    $detailsPopUp.html(detailsPopUpTpl({
        'artisan': artisan,
    }));

    Utils.updateUpdateRequestData('updateRequestFull', artisan);
}

function detailsPopUpShowCallback(event: any) {
    populatePopUpWithData(jQuery(event.relatedTarget).closest('tr').data('artisan'));
}

export function init(): (() => void)[] {
    return [
        () => {
            $detailsPopUp = jQuery('#artisanDetailsModal');

            $detailsPopUp.find('a[data-href]').each((index: number, element: HTMLElement) => {
                /* Grep code for WORKAROUND_PLACEHOLDERS_CREATING_FAKE_404S: data-href ---> href */
                element.setAttribute('href', element.getAttribute('data-href') || '');
                element.removeAttribute('data-href');
            });
            $detailsPopUp.on('show.bs.modal', detailsPopUpShowCallback);
        },
        () => {
            Handlebars.registerHelper({
                optional: optionalTplFunc,
                optionalList: optionalListTplFunc,
                commaSeparated: commaSeparatedTplFunc,
                photos: photosTplFunc,
            });
        },
        () => {
            detailsPopUpTpl = Handlebars.compile($detailsPopUp.html(), {
                assumeObjects: true,
                data: false,
                knownHelpersOnly: true,
                knownHelpers: {
                    'optional': true,
                    'optionalList': true,
                    'commaSeparated': true,
                    'photos': true,
                },
            });
        },
    ];
}
