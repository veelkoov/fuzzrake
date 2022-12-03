export function jqTarget(event: JQuery.Event): JQuery {
    // @ts-ignore 3.6
    return jQuery(event.relatedTarget || event.target);
}

export function getArtisanIndexForEvent(event: JQuery.Event): number {
    return jqTarget(event).closest('.artisan-data').data('index');
}
