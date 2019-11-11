'use strict';

require('../../3rd-party/flag-icon-css/css/flag-icon.css');

import * as DataTable from '../main/artisansTable';
import * as DetailsPopUp from '../main/detailsPopUp';
import * as AntiScamWarning from '../main/antiScamWarning';
import * as ArtisanPopUp from "../main/artisanPopUp";
import Artisan from '../class/Artisan';
import {makerIdHashRegexp} from "../consts";

function init(): void {
    let callbacks: (() => void)[] = [
        loadFuzzrakeData,
    ];
    callbacks.push(...ArtisanPopUp.init());
    callbacks.push(...AntiScamWarning.init());
    callbacks.push(...DataTable.init());
    callbacks.push(...DetailsPopUp.init());
    callbacks.push(finalizeInit);

    executeOneByOne(callbacks);
}

function executeOneByOne(callbacks): void {
    setTimeout(() => {
        let callback = callbacks.shift();

        if (callback) {
            callback();

            executeOneByOne(callbacks);
        }
    }, 10);
}

function loadFuzzrakeData(): void {
    // @ts-ignore
    window.loadFuzzrakeData();
}

function finalizeInit(): void {
    function openArtisanByFragment(hash: string): void {
        if (hash.match(makerIdHashRegexp)) {
            $(hash).children().eq(0).trigger('click');
        }
    }

    $('#data-loading-message, #data-table-container').toggle();

    openArtisanByFragment(window.location.hash);
}

export {Artisan, init};
