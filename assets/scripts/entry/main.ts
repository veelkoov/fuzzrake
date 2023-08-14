import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import DataManager from '../main/DataManager';
import Main from '../vue/Main.vue';
import Static from '../Static';
import {createApp} from 'vue';
import {getMessageBus} from '../main/MessageBus';

const messageBus = getMessageBus();

jQuery(() => {
    Static.loadFuzzrakeData();

    createApp(Main).mount('#main-primary-component');

    const dataManager = new DataManager(messageBus);

    Static.hideLoadingIndicator();
    messageBus.notifySetupFinished();
});
