import GTag from "./GTag";

export default class Tracking {
    private constructor() {
    }

    private static trackLinkClick($link: JQuery<HTMLElement>, category: string): void {
        let label = $link.attr('class').split(' ')
            .filter(value => value.startsWith('track-')).pop()
            .replace(/^track-/, '') || 'missing-label';

        if (label !== 'ignore') {
            GTag.event('link-click', {
                'event_category': category,
                'event_label': label,
            });
        }
    }

    public static setupOnLinks(selector: string, category: string) {
        jQuery(selector).on('click', function () {
            Tracking.trackLinkClick($(this), category);
        });
    }
}
