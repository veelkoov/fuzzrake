import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import Checkbox from '../class/Checkbox';
import Radio from '../class/fields/Radio';
import {NO, YES} from '../consts';
import {toggle} from '../jQueryUtils';

export default class ChecklistManager {
    private readonly illBeCareful: Checkbox;
    private readonly isAdult: Radio;
    private readonly wantsSfw: Radio;

    private config = AgeAndSfwConfig.getInstance();

    public constructor(
        private readonly $ageContainer: JQuery,
        private readonly $wantsSfwContainer: JQuery,
        private readonly $dismissButton: JQuery,
        private readonly dismissChecklistCallback: () => void,
        illBeCarefulCheckboxId: string,
        isAdultRadioFieldName: string,
        wantsSfwRadioFieldName: string,
    ) {
        this.illBeCareful = new Checkbox(illBeCarefulCheckboxId, () => this.refreshAll());
        this.isAdult = new Radio(isAdultRadioFieldName, () => this.refreshAll());
        this.wantsSfw = new Radio(wantsSfwRadioFieldName, () => this.refreshAll());

        if (this.config.getIsFilled()) {
            this.illBeCareful.check();

            if (!this.config.getIsAdult()) {
                this.isAdult.selectVal(NO);
            } else {
                this.isAdult.selectVal(YES);
                this.wantsSfw.selectVal(this.config.getWantsSfw() ? YES : NO);
            }
        }

        this.refreshAll();

        if (this.config.getMakerMode()) {
            this.dismissChecklistCallback();
        }
    }

    private isReady(): boolean {
        return this.illBeCareful.isChecked() && (this.isAdult.isVal(NO) || this.wantsSfw.isAnySelected());
    }

    private refreshAll(): void {
        toggle(this.$ageContainer, this.illBeCareful.isChecked());
        toggle(this.$wantsSfwContainer, this.illBeCareful.isChecked() && this.isAdult.isVal(YES));

        this.config.setWantsSfw(!this.wantsSfw.isVal(NO));
        this.config.setIsAdult(this.isAdult.isVal(YES));

        let label: string, disabled: boolean;

        if (this.isReady()) {
            label = 'I will now click this button';
            disabled = false;
        } else {
            label = "I can't click this button yet";
            disabled = true;
        }

        this.$dismissButton.val(label);
        this.$dismissButton.prop('disabled', disabled);
    }

    public getDismissButtonClickedCallback(): () => void {
        return () => this.dismissButtonOnClick();
    }

    private dismissButtonOnClick(): void {
        if (this.isReady()) {
            this.config.setIsFilled(true);
            this.config.save();

            this.dismissChecklistCallback();
        }
    }
}
