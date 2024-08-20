import "../../3rd-party/flag-icon-css/css/flag-icon.css";
import "../../styles/main.scss";
import DataManager from "../main/DataManager";
import Main from "../vue/Main.vue";
import Static from "../Static";
import { createApp } from "vue";
import { getMessageBus } from "../main/MessageBus";

const messageBus = getMessageBus();

jQuery(() => {
  Static.loadFuzzrakeData();

  createApp(Main).mount("#main-primary-component");

  // FIXME: https://github.com/veelkoov/fuzzrake/pull/187/files
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const dataManager = new DataManager(messageBus);

  Static.hideLoadingIndicator();
  messageBus.notifySetupFinished();
});
