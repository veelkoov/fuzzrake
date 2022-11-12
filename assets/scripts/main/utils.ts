export function getArtisanIndexForEvent(event: any): number {
    return jQuery(event.relatedTarget).closest('.artisan-data').data('index');
}
