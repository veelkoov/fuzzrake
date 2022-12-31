import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import ChecklistManager from '../main/ChecklistManager';
import DataManager from '../main/DataManager';
import FiltersManager from '../main/FiltersManager';
import Main from '../vue/Main.vue';
import Static from '../Static';
import {createApp} from 'vue';
import {getMessageBus} from '../main/MessageBus';
import {makerIdHashRegexp} from '../consts';

function loadFuzzrakeData(): void {
    // @ts-ignore grep-window-load-fuzzrake-data
    window.loadFuzzrakeData();
}

function openArtisanByFragment(): void {
    if (window.location.hash.match(makerIdHashRegexp)) {
        let makerId = window.location.hash.slice(1);

        if (makerId in Static.getMakerIdsMap()) {
            makerId = Static.getMakerIdsMap()[makerId];
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

const messageBus = getMessageBus();

jQuery(() => {
    loadFuzzrakeData();

    createApp(Main).mount('#main-primary-component');

    const checklistManager = new ChecklistManager(
        jQuery('#checklist-age-container'),
        jQuery('#checklist-wants-sfw-container'),
        jQuery('#checklist-dismiss-btn'),
        dismissChecklistCallback,
        'checklist-ill-be-careful',
        'checklistIsAdult',
        'checklistWantsSfw',
    );
    const dismissButtonClickedCallback = checklistManager.getDismissButtonClickedCallback();
    jQuery('#checklist-dismiss-btn').on(
        'click',
        () => {
            filtersManager.triggerUpdate();
            dismissButtonClickedCallback();
        },
    );

    const dataManager = new DataManager(
        messageBus,
    );

    const filtersManager = new FiltersManager(
        messageBus,
        jQuery('#filters'),
    );
    jQuery('#filtersModal').on(
        'hidden.bs.modal',
        filtersManager.getTriggerUpdateCallback(),
    );

    jQuery('#data-table-container').toggle();
    Static.hideLoadingIndicator();

    openArtisanByFragment();
});
