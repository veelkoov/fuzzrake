'use strict';

import * as $ from 'jquery';
import * as DataTable from './dataTable';
import * as DetailsModal from './detailsModal';
import * as Utils from './utils'
import Artisan from './Artisan';
import {makerIdHashRegexp} from "../consts";

require('../../3rd-party/flag-icon-css/css/flag-icon.css');

declare var ARTISANS: Artisan[];
declare var MAKER_IDS_MAP: {string: string};

function initRequestUpdateModal(): void {
    $('#updateRequestModal').on('show.bs.modal', function (event) {
        updateRequestUpdateModalWithRowData($(event.relatedTarget).closest('tr').data('artisan'));
    });
}

function addReferrerRequestTooltip(): void {
    $('div.artisan-links')
        .attr('title', 'If you\'re going to contact the studio/maker, <u>please let them' +
            ' know you found them here</u>! This will help us all a lot. Thank you!')
        .data('placement', 'top')
        .data('boundary', 'window')
        .data('html', true)
        .data('fallbackPlacement', [])
        .tooltip();
}

function updateRequestUpdateModalWithRowData(artisan): void {
    $('#artisanNameUR').html(artisan.name);

    Utils.updateUpdateRequestData('updateRequestSingle', artisan);
}

function openArtisanByFragment(hash: string): void {
    if (hash.match(makerIdHashRegexp)) {
        $(hash).children().eq(0).trigger('click');
    }
}

function scrollToTopOfTheDataTable(): void {
    // Anti-scam warning causes the user to be at the bottom of the table
    window.scrollTo(0, $('#data-table-container').offset().top - 70); // FIXME: 70!!!
}

function initAfterDataLoaded(): void {
    if (null === MAKER_IDS_MAP || null === ARTISANS) {
        return;
    }

    DataTable.init();
    DetailsModal.init();

    initRequestUpdateModal();
    addReferrerRequestTooltip();

    openArtisanByFragment(window.location.hash);

    $('#data-loading-message, #data-table-container').toggle();
}

function loadArtisanData(artisansApiUrl: string): void {
    $.ajax(artisansApiUrl, {
        dataType: 'json',
        error: jqXHR => {
            alert('Failed to load data');
        },
        success: data => {
            ARTISANS = data;
            initAfterDataLoaded();
        }
    });
}

function loadMakerIdMapData(makerIdsApiUrl: string): void {
    $.ajax(makerIdsApiUrl, {
        dataType: 'json',
        error: jqXHR => {
            alert('Failed to load data');
        },
        success: data => {
            MAKER_IDS_MAP = data;
            initAfterDataLoaded();
        }
    });
}

function init(artisansApiUrl: string, makerIdsApiUrl: string): void {
    $('#scam-risk-acknowledgement').on('click', (event) => {
        $('#scam-risk-warning, #scam-risk-acknowledged').toggle();
        scrollToTopOfTheDataTable();
        event.preventDefault();
    });

    loadArtisanData(artisansApiUrl);
    loadMakerIdMapData(makerIdsApiUrl);
}

export {Artisan, init};
