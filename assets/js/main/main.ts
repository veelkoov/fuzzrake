'use strict';

import * as $ from 'jquery';
import * as DataTable from './dataTable';
import * as DetailsModal from './detailsModal';
import * as Utils from './utils'
import Artisan from './Artisan';

require('../../3rd-party/flag-icon-css/css/flag-icon.css');

function initRequestUpdateModal() {
    $('#updateRequestModal').on('show.bs.modal', function (event) {
        updateRequestUpdateModalWithRowData($(event.relatedTarget).closest('tr').data('artisan'));
    });
}

function addReferrerRequestTooltip() {
    $('div.artisan-links')
        .attr('title', 'If you\'re going to contact the studio/maker, <u>please let them' +
            ' know you found them here</u>! This will help us all a lot. Thank you!')
        .data('placement', 'top')
        .data('boundary', 'window')
        .data('html', true)
        .data('fallbackPlacement', [])
        .tooltip();
}

function updateRequestUpdateModalWithRowData(artisan) {
    $('#artisanNameUR').html(artisan.name);

    Utils.updateUpdateRequestData('updateRequestSingle', artisan);
}

function openArtisanByFragment(hash) {
    if (hash) {
        $(hash).children().eq(0).trigger('click');
    }
}

function scrollToTopOfTheDataTable() {
    // Anti-scam warning causes the user to be at the bottom of the table
    window.scrollTo(0, $('#data-table-container').offset().top - 70); // FIXME: 70!!!
}

export function init() {
    $('#scam-risk-acknowledgement').on('click', (event) => {
        $('#scam-risk-warning, #scam-risk-acknowledged').toggle();
        scrollToTopOfTheDataTable();
        event.preventDefault();
    });

    DataTable.init();
    DetailsModal.init();

    initRequestUpdateModal();
    addReferrerRequestTooltip();

    openArtisanByFragment(window.location.hash);

    $('#data-loading-message, #data-table-container').toggle();
}

export {Artisan};
