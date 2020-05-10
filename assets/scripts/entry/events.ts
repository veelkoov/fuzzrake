require('../../styles/events.less');

jQuery(function (): void {
    jQuery('#events-list .toggle-details').on('click', (event: Event) => {
        event.preventDefault();

        jQuery(event.target).parents('.events-item')
            .find('.event-details')
            .toggle();
    });
});
