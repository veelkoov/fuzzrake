'use strict';

$(function (): void {
    $('#events-list .toggle-details').on('click', (event: Event) => {
        event.preventDefault();

        $(event.target).parents('.events-item')
            .find('.event-details')
            .toggle();
    });
});
