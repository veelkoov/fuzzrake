import * as Handlebars from 'handlebars/runtime';
import CardPopUpManager from "../main/CardPopUpManager";
import ChecklistManager from '../main/ChecklistManager';
import DataBridge from '../data/DataBridge';
import DataManager from '../main/DataManager';
import FiltersButtonManager from '../main/FiltersButtonManager';
import FiltersManager from '../main/FiltersManager';
import HandlebarsHelpers from '../class/HandlebarsHelpers';
import TableManager from '../main/TableManager';
import UpdatePopUpManager from "../main/UpdatePopUpManager";
import {makerIdHashRegexp} from '../consts';

import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';

function loadFuzzrakeData(): void {
    // @ts-ignore
    window.loadFuzzrakeData();
}

function openArtisanByFragment(): void {
    if (window.location.hash.match(makerIdHashRegexp)) {
        let makerId = window.location.hash.slice(1);

        if (makerId in DataBridge.getMakerIdsMap()) {
            makerId = DataBridge.getMakerIdsMap()[makerId];
        }

        jQuery('#' + makerId).children().eq(0).trigger('click');
    }
}

function dismissChecklistCallback(): void {
    // TODO: Update/run filters
    jQuery('#checklist-container, #data-table-content-container').toggle();

    // Checklist causes the user to be at the bottom of the table when it shows up
    let offset = jQuery('#data-table-content-container').offset() || {'top': 5};
    window.scrollTo(0, offset.top - 5);
}

let dataManager: DataManager;
let filtersManager: FiltersManager;
let filtersButtonManager: FiltersButtonManager;
let tableManager: TableManager;
let checklistManager: ChecklistManager;
let cardPopUpManager: CardPopUpManager;
let updatePopUpManager: UpdatePopUpManager;

jQuery(() => {
    Handlebars.registerHelper(HandlebarsHelpers.getHelpersToRegister());

    loadFuzzrakeData();

    checklistManager = new ChecklistManager(
        jQuery('#checklist-age-container'),
        jQuery('#checklist-wants-sfw-container'),
        jQuery('#checklist-dismiss-btn'),
        dismissChecklistCallback,
        'checklist-ill-be-careful',
        'checklistIsAdult',
        'checklistWantsSfw',
    );
    jQuery('#checklist-dismiss-btn').on(
        'click',
        checklistManager.getDismissButtonClickedCallback(),
    );

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

    updatePopUpManager = new UpdatePopUpManager(
        dataManager,
        jQuery('#artisanUpdatesModalContent'),
    );
    jQuery('#artisanUpdatesModal').on(
        'show.bs.modal',
        updatePopUpManager.getShowCallback(),
    );

    cardPopUpManager = new CardPopUpManager(
        dataManager,
        jQuery('#artisanDetailsModalContent'),
    );
    jQuery('#artisanDetailsModal').on(
        'show.bs.modal',
        cardPopUpManager.getShowCallback(),
    );

    jQuery('#data-loading-message, #data-table-container').toggle();

    openArtisanByFragment();
});
