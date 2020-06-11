import * as Handlebars from "handlebars";

const escape = Handlebars.Utils.escapeExpression;

export default class HandlebarsHelpers {
    private static readonly HTML_SIGN_UNKNOWN = new Handlebars.SafeString('<i class="fas fa-question-circle" title="Unknown"></i>');

    private constructor() {
    }

    public static getHelpersToRegister(): {} {
        return {
            optional: HandlebarsHelpers.optional,
            optionalList: HandlebarsHelpers.optionalList,
            commaSeparated: HandlebarsHelpers.commaSeparated,
            photos: HandlebarsHelpers.photos,
            has: HandlebarsHelpers.has,
        };
    }

    public static getKnownHelpersObject(): {} {
        return {
            'optional': true,
            'optionalList': true,
            'commaSeparated': true,
            'photos': true,
            'has': true,
        };
    }

    public static commaSeparated(list: string[] | Set<string>): string {
        if (list instanceof Set) {
            list = Array.from(list);
        }

        return list.join(', ');
    }

    public static has(subject: any): boolean {
        if (subject instanceof Set) {
            return subject.size > 0;
        }

        if (subject instanceof Array) {
            return subject.length > 0;
        }

        return subject !== null && subject !== '';
    }

    public static optional(element: string | string[] | Set<string>): string | object {
        if (element instanceof Set) {
            element = Array.from(element);
        }

        if (element instanceof Array) {
            element = element.join(', ');
        }

        return element !== '' ? element : HandlebarsHelpers.HTML_SIGN_UNKNOWN;
    }

    public static optionalList(list: string[] | Set<string>): string | object {
        if (list instanceof Set) {
            list = Array.from(list);
        }

        let rendered = list.map(function (value: string): string {
            return `<li>${escape(value)}</li>`;
        }).join('');

        return rendered ? new Handlebars.SafeString(`<ul>${rendered}</ul>`) : HandlebarsHelpers.HTML_SIGN_UNKNOWN;
    }

    public static photos(miniatures: string[], photos: string[]): string | object {
        if (miniatures.length === 0 || miniatures.length !== photos.length) {
            return '';
        }

        let result: string = '';

        for (let i: number = 0; i < miniatures.length; i++) {
            result += `<div><a href="${escape(photos[i])}" target="_blank"><img src="${escape(miniatures[i])}" alt="" /></a></div>`;
        }

        return new Handlebars.SafeString(`<div class="imgs-container">${result}</div>`);
    }
}
