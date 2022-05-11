import * as AntiScamWarning from '../main/antiScamWarning';
import * as DataTable from '../main/artisansTable';
import * as DetailsPopUp from '../main/detailsPopUp';
import * as Handlebars from 'handlebars/runtime';
import * as UpdateRequestPopUp from '../main/updateRequestPopUp';
import Artisan from '../class/Artisan';
import DataBridge from '../class/DataBridge';
import HandlebarsHelpers from "../class/HandlebarsHelpers";
import Species from '../species/Species';
import {makerIdHashRegexp} from '../consts';

import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.less';

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
    window.loadFuzzrakeData(Artisan);
}

function finalizeInit(): void {
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

jQuery(function () {
    let callbacks: (() => void)[] = [
        loadFuzzrakeData,
    ];
    callbacks.push(...Species.initWithArtisansUpdate()); // FIXME: Artisans should be completely initialized in one step
    callbacks.push(() => Handlebars.registerHelper(HandlebarsHelpers.getHelpersToRegister()))
    callbacks.push(...UpdateRequestPopUp.init());
    callbacks.push(...AntiScamWarning.init());
    callbacks.push(...DataTable.init());
    callbacks.push(...DetailsPopUp.init());
    callbacks.push(finalizeInit);

    executeOneByOne(callbacks);
});
