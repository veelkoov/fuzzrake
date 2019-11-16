'use strict';

require('../../css/events.less');

$(function (): void {
    $('#events-list .toggle-details').on('click', (event: Event) => {
        event.preventDefault();

        $(event.target).parents('.events-item')
            .find('.event-details')
            .toggle();
    });
});
