import * as Handlebars from "handlebars/runtime";

const escape = Handlebars.Utils.escapeExpression;

export default class HandlebarsHelpers {
    private static readonly MONTHS = {
        '01': 'Jan',
        '02': 'Feb',
        '03': 'Mar',
        '04': 'Apr',
        '05': 'May',
        '06': 'Jun',
        '07': 'Jul',
        '08': 'Aug',
        '09': 'Sep',
        '10': 'Oct',
        '11': 'Nov',
        '12': 'Dec',
    };

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
            since: HandlebarsHelpers.since,
        };
    }

    public static getKnownHelpersObject(): {} {
        return {
            'optional': true,
            'optionalList': true,
            'commaSeparated': true,
            'photos': true,
            'has': true,
            'since': true,
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

    public static since(element: string): string | object {
        if (element !== '') {
            let parts = element.split('-');

            element = HandlebarsHelpers.MONTHS[parts[1]] + ' ' + parts[0];
        }

        return HandlebarsHelpers.optional(element);
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
