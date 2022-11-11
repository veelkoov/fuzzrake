import * as Checklist from '../main/checklist';
import * as DetailsPopUp from '../main/detailsPopUp';
import * as Filters from '../main/filters';
import * as Handlebars from 'handlebars/runtime';
import * as UpdateRequestPopUp from '../main/updateRequestPopUp';
import DataBridge from '../data/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import {makerIdHashRegexp} from '../consts';

import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import DataManager from "../main/DataManager";

function executeOneByOne(callbacks): void {
    setTimeout(() => {
        let callback = callbacks.shift();

        if (callback) {
            callback();

            executeOneByOne(callbacks);
        }
    }, 1);
}

function loadFuzzrakeData(): void {
    // @ts-ignore
    window.loadFuzzrakeData();
}

function finalizeInit(): void {
    // TODO
    dataManager.updateQuery($('#filters').serialize());

    function openArtisanByFragment(hash: string): void {
        if (hash.match(makerIdHashRegexp)) {
            let makerId = hash.slice(1);

            if (makerId in DataBridge.getMakerIdsMap()) {
                makerId = DataBridge.getMakerIdsMap()[makerId];
            }

            jQuery('#' + makerId).children().eq(0).trigger('click');
        }
    }

    jQuery('#data-loading-message, #data-table-container').toggle();

    openArtisanByFragment(window.location.hash);
}

function getDataUpdatedCallback(dataManager: DataManager): void {
    const $tBody = jQuery('#artisans tbody');

    dataManager.getData.forEach((value, index) => $tBody.append(`<tr data-index="${index}" class="artisan-data"><td class="name" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">${value[2]}</td><td class="maker-id" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">${value[0]}</td></tr>`)); // TODO: Recreate original structure (name cell etc.)
}

const dataManager = new DataManager(getDataUpdatedCallback);

jQuery(function () {

    let callbacks: (() => void)[] = [
        loadFuzzrakeData,
    ];
    callbacks.push(() => Handlebars.registerHelper(HandlebarsHelpers.getHelpersToRegister()))
    callbacks.push(...UpdateRequestPopUp.init(dataManager));
    callbacks.push(...Filters.init());
    callbacks.push(...DetailsPopUp.init(dataManager));
    callbacks.push(...Checklist.init());
    callbacks.push(finalizeInit);

    executeOneByOne(callbacks);
});
