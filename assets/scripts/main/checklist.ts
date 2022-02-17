import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import Checkbox from "../class/Checkbox";
import Storage from "../class/Storage";
import Radio from "../class/Radio";
import {applyFilters} from "./filters";
import {NO, YES} from "../consts";
import {toggle} from "../jQueryUtils";

let illBeCareful: Checkbox, ackProsAndCons: Checkbox, iLikeButtons: Checkbox;
let isAdult: Radio, wantsSfw: Radio;
let $prosConsContainer: JQuery<HTMLElement>, $ageContainer: JQuery<HTMLElement>,
    $wantsSfwContainer: JQuery<HTMLElement>, $dismissButton: JQuery<HTMLElement>;

const config = new AgeAndSfwConfig();

const S_WANTS_SFW = 'wantsSfw';
const S_FILLED = 'filled';
const S_IS_ADULT = 'isAdult';

function isReady(): boolean {
    return illBeCareful.isChecked() && ackProsAndCons.isChecked() && (isAdult.isVal(NO) || wantsSfw.isAnySelected());
}

function refreshAll(): void {
    toggle($prosConsContainer, illBeCareful.isChecked());
    toggle($ageContainer, illBeCareful.isChecked() && ackProsAndCons.isChecked());
    toggle($wantsSfwContainer, illBeCareful.isChecked() && ackProsAndCons.isChecked() && isAdult.isVal(YES));

    config.setWantsSfw(!wantsSfw.isVal(NO));
    config.setIsAdult(isAdult.isVal(YES));

    let emoticon: string, label: string;

    if (isReady()) {
        label = 'I will now click this button';
        emoticon = ' :)';
        $dismissButton.addClass('btn-primary');
        $dismissButton.removeClass('btn-secondary');
    } else {
        label = "I can't click this button yet";
        emoticon = ' :(';
        $dismissButton.removeClass('btn-primary');
        $dismissButton.addClass('btn-secondary');
    }

    $dismissButton.val(label + (iLikeButtons.isChecked() ? emoticon : ''));
}

function dismiss(): void {
    if (isReady()) {
        applyFilters();

        jQuery('#checklist-container, #checklist-done').toggle();

        // Checklist causes the user to be at the bottom of the table when it shows up
        let offset = jQuery('#data-table-container').offset() || { 'top': 5 };
        window.scrollTo(0, offset.top - 5);

        Storage.saveBoolean(S_FILLED, true, 3600);
        Storage.saveBoolean(S_IS_ADULT, isAdult.isVal(YES), 3600)
        Storage.saveBoolean(S_WANTS_SFW, !wantsSfw.isVal(NO), 3600);
    }
}

export function init(): (() => void)[] {
    return [
        () => {
            $prosConsContainer = jQuery('#checklist-pros-and-cons-container');
            $ageContainer = jQuery('#checklist-age-container');
            $wantsSfwContainer = jQuery('#checklist-wants-sfw-container');
            $dismissButton = jQuery('#checklist-dismiss-btn');
            $dismissButton.on('click', dismiss);

            illBeCareful = new Checkbox('checklist-ill-be-careful', refreshAll);
            ackProsAndCons = new Checkbox('checklist-ack-pros-and-cons', refreshAll);
            iLikeButtons = new Checkbox('checklist-i-like-buttons', refreshAll);

            isAdult = new Radio('checklistIsAdult', refreshAll);
            wantsSfw = new Radio('checklistWantsSfw', refreshAll);

            if (Storage.getBoolean(S_FILLED)) {
                illBeCareful.check();
                ackProsAndCons.check();

                if (!Storage.getBoolean(S_IS_ADULT)) {
                    isAdult.selectVal(NO);
                } else {
                    isAdult.selectVal(YES);
                    wantsSfw.selectVal(Storage.getBoolean(S_WANTS_SFW) ? YES : NO);
                }
            }
        },
        () => {
            refreshAll();
        },
    ];
}

export function getAgeAndSfwConfig(): AgeAndSfwConfig {
    return config;
}
