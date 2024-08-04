import Checkbox from '../class/Checkbox';
import DynamicRadio from '../class/fields/DynamicRadio';
import AgeAndSfwConfig from '../class/AgeAndSfwConfig';

export default class Checklist {
    private aasConfig = AgeAndSfwConfig.getInstance();

    private illBeCareful: Checkbox;
    private userAge: DynamicRadio;
    private wantsSfw: DynamicRadio;
    private $button: JQuery<HTMLElement>;

    public constructor() {
        this.illBeCareful = new Checkbox('checklist-ill-be-careful', () => this.refresh());
        this.userAge = new DynamicRadio('checklist-user-age', '#checklist-age-section', () => this.refresh(), true)
        this.wantsSfw = new DynamicRadio('checklist-wants-sfw', '#checklist-nsfw-section', () => this.refresh(), true)
        this.$button = jQuery('#checklist-dismiss-btn');
        this.$button.on('click', () => this.save());

        if (this.aasConfig.getIsFilled()) {
            this.illBeCareful.check();
            this.userAge.selectVal(this.aasConfig.getIsAdult() ? 'adult' : 'minor');
            this.wantsSfw.selectVal(this.aasConfig.getWantsSfw() ? 'yes' : 'no');
        }

        this.refresh();
    }

    public refresh(): void {
        this.userAge.toggle(this.illBeCareful.isChecked);
        this.wantsSfw.toggle(this.illBeCareful.isChecked && this.isAdultOptionSelected);

        this.$button.prop('disabled', !this.isReady);
        this.$button.attr('value', this.isReady ? 'I will now click this button' : "I can't click this button yet");
    }

    public get isReady(): boolean {
        return this.illBeCareful.isChecked && (
            this.userAge.isVal('minor') || this.isAdultOptionSelected && this.wantsSfw.isAnySelected()
        );
    }

    private get isAdultOptionSelected() {
        return this.userAge.isVal('adult');
    }

    public save(): void {
        this.aasConfig.setIsAdult(this.isAdultOptionSelected);
        this.aasConfig.setWantsSfw(!this.wantsSfw.isVal('no'));
        this.aasConfig.setIsFilled(true);
        this.aasConfig.save();
    }
}
