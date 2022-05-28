import * as Handlebars from 'handlebars/runtime';
import Artisan from './Artisan';
import {ADULTS, ADULTS_DESC, MINORS, MINORS_DESC, MIXED, MIXED_DESC} from '../consts';
import {SafeString} from 'handlebars/runtime';

type TplString = string | SafeString;
const escape = Handlebars.Utils.escapeExpression;
const HTML_SIGN_UNKNOWN = new SafeString('<i class="fas fa-question-circle" title="Unknown"></i>')

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

    private constructor() {
    }

    public static getHelpersToRegister(): {} {
        return {
            optional:             HandlebarsHelpers.optional,
            optionalList:         HandlebarsHelpers.optionalList,
            commaSeparated:       HandlebarsHelpers.commaSeparated,
            photos:               HandlebarsHelpers.photos,
            hasPhotos:            HandlebarsHelpers.hasPhotos,
            has:                  HandlebarsHelpers.has,
            since:                HandlebarsHelpers.since,
            nl2br:                HandlebarsHelpers.nl2br,
            describeAges:         HandlebarsHelpers.describeAges,
            describeCompleteness: HandlebarsHelpers.describeCompleteness,
            isPossiblyUnsafe:     HandlebarsHelpers.isPossiblyUnsafe,
        };
    }

    public static tplCfg(): {} {
        let knownHelpers = {};

        for (let key in this.getHelpersToRegister()) {
            knownHelpers[key] = true;
        }

        return {
            assumeObjects: true,
            data: false,
            knownHelpersOnly: true,
            knownHelpers: knownHelpers,
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

    public static optional(element: string | string[] | Set<string>): TplString {
        if (element instanceof Set) {
            element = Array.from(element);
        }

        if (element instanceof Array) {
            element = element.join(', ');
        }

        return element !== '' ? element : HTML_SIGN_UNKNOWN;
    }

    public static since(element: string): string | object {
        if (element !== '') {
            let parts = element.split('-');

            element = HandlebarsHelpers.MONTHS[parts[1]] + ' ' + parts[0];
        }

        return HandlebarsHelpers.optional(element);
    }

    public static optionalList(list: string[] | Set<string>): TplString {
        if (list instanceof Set) {
            list = Array.from(list);
        }

        let rendered = list.map(function (value: string): string {
            return `<li>${escape(value)}</li>`;
        }).join('');

        return rendered ? new SafeString(`<ul>${rendered}</ul>`) : HTML_SIGN_UNKNOWN;
    }

    public static photos(artisan: Artisan): TplString {
        if (!HandlebarsHelpers.hasPhotos(artisan)) {
            return '';
        }

        let result: string = '';

        for (let i: number = 0; i < artisan.miniatureUrls.length; i++) {
            result += `<div><a href="${escape(artisan.photoUrls[i])}" target="_blank"><img src="${escape(artisan.miniatureUrls[i])}" alt="" /></a></div>`;
        }

        return new SafeString(`<div class="imgs-container">${result}</div>`);
    }

    public static hasPhotos(artisan: Artisan): boolean {
        return artisan.photoUrls.length !== 0 && artisan.miniatureUrls.length === artisan.photoUrls.length;
    }

    public static nl2br(element: TplString): SafeString {
        if (element instanceof SafeString) {
            return element; // FIXME: https://github.com/veelkoov/fuzzrake/issues/111
        }

        return new SafeString(element.split("\n").map(value => escape(value)).join('<br />'));
    }

    public static describeAges(artisan: Artisan): TplString {
        switch (artisan.ages) {
            case MINORS:
                return new SafeString(MINORS_DESC + ' <i class="ages fa-solid fa-user-minus"></i>');
            case MIXED:
                return new SafeString(MIXED_DESC + ' <i class="ages fa-solid fa-user-plus"></i> <i class="ages fa-solid fa-user-minus"></i>');
            case ADULTS:
                return ADULTS_DESC;
        }

        if (true === artisan.isMinor) {
            return new SafeString(MINORS_DESC + ' <i class="ages fa-solid fa-user-minus"></i>');
        } else if (false === artisan.isMinor) {
            return ADULTS_DESC;
        }

        return new SafeString(HTML_SIGN_UNKNOWN.toString() + ' <i class="ages fa-solid fa-user"></i>');
    }

    public static isPossiblyUnsafe(artisan: Artisan): boolean {
        return false !== artisan.nsfwWebsite || false !== artisan.nsfwSocial || false !== artisan.doesNsfw;
    }

    private static describeCompleteness(artisan: Artisan): string {
        if (artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_PERFECT) {
            return 'Awesome! ❤️';
        } else if (artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_GREAT) {
            return 'Great!'
        } else if (artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_GOOD) {
            return 'Good job!'
        } else if (artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_OK) {
            return 'Some updates might be helpful...';
        } else {
            return 'Yikes! :( Updates needed!';
        }
    }
}
