export function getArtisanFromRelated(event: any) {
    return jQuery(event.relatedTarget).closest('.artisan-data').data('artisan');
}
