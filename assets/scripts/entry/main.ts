import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import DataManager from '../main/DataManager';
import Main from '../vue/Main.vue';
import Static from '../Static';
import {createApp} from 'vue';
import {getMessageBus} from '../main/MessageBus';
import {makerIdHashRegexp} from '../consts';

const isMakerMode = AgeAndSfwConfig.getInstance().getMakerMode();
const messageBus = getMessageBus();

jQuery(() => {
    Static.loadFuzzrakeData();

    createApp(Main).mount('#main-primary-component');

    const dataManager = new DataManager(messageBus);

    jQuery('#data-table-container').toggle();

    if (isMakerMode) {
        messageBus.requestDataLoad('', false);
    } else {
        Static.hideLoadingIndicator();
    }

    openArtisanByFragment();
});

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

    if (!isMakerMode) {
        messageBus.requestDataLoad('wantsSfw=0&isAdult=1&makerId=' + makerId, true);
    }
}
