import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import Checkbox from "../class/Checkbox";
import Radio from "../class/fields/Radio";
import {applyFilters} from "./filters";
import {NO, YES} from "../consts";
import {toggle} from "../jQueryUtils";

let illBeCareful: Checkbox, ackProsAndCons: Checkbox, iLikeButtons: Checkbox;
let isAdult: Radio, wantsSfw: Radio;
let $prosConsContainer: JQuery<HTMLElement>, $ageContainer: JQuery<HTMLElement>,
    $wantsSfwContainer: JQuery<HTMLElement>, $dismissButton: JQuery<HTMLElement>;

const config = AgeAndSfwConfig.getInstance();

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

function dismissChecklist(): void {
    applyFilters();

    jQuery('#checklist-container, #data-table-content-container').toggle();

    // Checklist causes the user to be at the bottom of the table when it shows up
    let offset = jQuery('#data-table-content-container').offset() || {'top': 5};
    window.scrollTo(0, offset.top - 5);
}

function dismissButtonOnClick(): void {
    if (isReady()) {
        config.setIsFilled(true);
        config.save();

        dismissChecklist();
    }
}

export function init(): (() => void)[] {
    return [
        () => {
            $prosConsContainer = jQuery('#checklist-pros-and-cons-container');
            $ageContainer = jQuery('#checklist-age-container');
            $wantsSfwContainer = jQuery('#checklist-wants-sfw-container');
            $dismissButton = jQuery('#checklist-dismiss-btn');
            $dismissButton.on('click', dismissButtonOnClick);

            illBeCareful = new Checkbox('checklist-ill-be-careful', refreshAll);
            ackProsAndCons = new Checkbox('checklist-ack-pros-and-cons', refreshAll);
            iLikeButtons = new Checkbox('checklist-i-like-buttons', refreshAll);

            isAdult = new Radio('checklistIsAdult', refreshAll);
            wantsSfw = new Radio('checklistWantsSfw', refreshAll);

            if (config.getIsFilled()) {
                illBeCareful.check();
                ackProsAndCons.check();

                if (!config.getIsAdult()) {
                    isAdult.selectVal(NO);
                } else {
                    isAdult.selectVal(YES);
                    wantsSfw.selectVal(config.getWantsSfw() ? YES : NO);
                }
            }
        },
        () => {
            refreshAll();
        },
        () => {
            if (config.getMakerMode()) {
                dismissChecklist();
            }
        },
    ];
}

export function getAgeAndSfwConfig(): AgeAndSfwConfig {
    return config;
}
