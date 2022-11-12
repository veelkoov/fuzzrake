import * as Checklist from '../main/checklist';
import * as DetailsPopUp from '../main/detailsPopUp';
import * as Handlebars from 'handlebars/runtime';
import * as UpdateRequestPopUp from '../main/updateRequestPopUp';
import DataBridge from '../data/DataBridge';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import DataManager from '../main/DataManager';
import FiltersManager from '../main/FiltersManager';
import {makerIdHashRegexp} from '../consts';
import FiltersButtonManager from '../main/FiltersButtonManager';

import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import TableManager from "../main/TableManager";

function loadFuzzrakeData(): void {
    // @ts-ignore
    window.loadFuzzrakeData();
}

function openArtisanByFragment(): void {
    const hash = window.location.hash;

    if (!hash.match(makerIdHashRegexp)) {
        return;
    }

    let makerId = hash.slice(1);

    if (makerId in DataBridge.getMakerIdsMap()) {
        makerId = DataBridge.getMakerIdsMap()[makerId];
    }

    jQuery('#' + makerId).children().eq(0).trigger('click');
}

let dataManager: DataManager;
let filtersManager: FiltersManager;
let filtersButtonManager: FiltersButtonManager;
let tableManager: TableManager;

jQuery(() => {
    Handlebars.registerHelper(HandlebarsHelpers.getHelpersToRegister());

    loadFuzzrakeData();

    tableManager = new TableManager(
        jQuery('#artisans tbody'),
    );

    dataManager = new DataManager(
        tableManager,
    );

    filtersButtonManager = new FiltersButtonManager(
        jQuery('#filtersButton'),
    );

    filtersManager = new FiltersManager(
        filtersButtonManager,
        dataManager,
        jQuery('#filters'),
    );

    jQuery('#filtersModal').on(
        'hidden.bs.modal',
        filtersManager.getTriggerUpdateCallback(),
    );

    filtersManager.triggerUpdate();

    UpdateRequestPopUp.init(dataManager);
    DetailsPopUp.init(dataManager);
    Checklist.init();

    jQuery('#data-loading-message, #data-table-container').toggle();

    openArtisanByFragment();
});
