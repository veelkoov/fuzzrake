import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import ChecklistManager from '../main/ChecklistManager';
import DataManager from '../main/DataManager';
import FiltersManager from '../main/FiltersManager';
import Main from '../vue/Main.vue';
import Static from '../Static';
import {createApp} from 'vue';
import {getMessageBus} from '../main/MessageBus';
import {makerIdHashRegexp} from '../consts';

function openArtisanByFragment(): void {
    if (!window.location.hash.match(makerIdHashRegexp)) {
        return;
    }

    let makerId = window.location.hash.slice(1);

    if (makerId in Static.getMakerIdsMap()) {
        makerId = Static.getMakerIdsMap()[makerId];
    }

    let opened: boolean = false;

    messageBus.listenTableUpdated(() => {
        if (!opened) {
            opened = true;

            jQuery('#' + makerId).children().eq(0).trigger('click');
        }
    });

    if (!AgeAndSfwConfig.getInstance().getMakerMode()) {
        messageBus.requestDataLoad('wantsSfw=0&isAdult=1&makerId=' + makerId, true);
    }
}

function dismissChecklist(): void {
    jQuery('#checklist-container, #data-table-content-container').toggle();

    // Checklist causes the user to be at the bottom of the table when it shows up
    let offset = jQuery('#data-table-content-container').offset() || {'top': 5};
    window.scrollTo(0, offset.top - 5);
}

const messageBus = getMessageBus();

jQuery(() => {
    Static.loadFuzzrakeData();

    createApp(Main).mount('#main-primary-component');

    const checklistManager = new ChecklistManager(
        jQuery('#checklist-age-container'),
        jQuery('#checklist-wants-sfw-container'),
        jQuery('#checklist-dismiss-btn'),
        dismissChecklist,
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
    );
    jQuery('#filtersModal').on(
        'hidden.bs.modal',
        filtersManager.getTriggerUpdateCallback(),
    );

    jQuery('#data-table-container').toggle();
    Static.hideLoadingIndicator();

    if (AgeAndSfwConfig.getInstance().getMakerMode()) {
        filtersManager.triggerUpdate();
    }

    openArtisanByFragment();
});
