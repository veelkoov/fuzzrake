'use strict';

import $ from 'jquery';
import * as DataTable from './dataTable';
import * as DetailsModal from './detailsModal';
import * as Consts from './consts';
import * as Utils from './utils'

require('../../3rd-party/flag-icon-css/css/flag-icon.css');

function initRequestUpdateModal() {
    $('#updateRequestModal').on('show.bs.modal', function (event) {
        updateRequestUpdateModalWithRowData($(event.relatedTarget).closest('tr').data('artisan'));
    });

    Utils.makeLinksOpenNewTab('#updateRequestModal a');
}

function addReferrerRequestTooltip() {
    $('div.artisan-links')
        .attr('title', Consts.REFERRER_HTML)
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
        $(hash).children().eq(0).click();
    }
}

$(() => {
    DataTable.init();
    DetailsModal.init();

    initRequestUpdateModal();
    addReferrerRequestTooltip();
    Utils.makeLinksOpenNewTab('#artisans a:not(.request-update)');

    openArtisanByFragment(window.location.hash);
});
