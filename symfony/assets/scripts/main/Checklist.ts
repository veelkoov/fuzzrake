import AgeAndSfwConfig from "../class/AgeAndSfwConfig";
import Checkbox from "../class/Checkbox";
import DynamicRadio from "../class/fields/DynamicRadio";
import { requireJQ } from "../jQueryUtils";

export default class Checklist {
  private aasConfig = AgeAndSfwConfig.getInstance();

  private illBeCareful: Checkbox;
  private isAdult: DynamicRadio;
  private wantsSfw: DynamicRadio;
  private $button: JQuery<HTMLElement>;

  public constructor() {
    this.illBeCareful = new Checkbox("checklist-ill-be-careful", () =>
      this.refresh(),
    );
    this.isAdult = new DynamicRadio(
      "isAdult",
      "#checklist-age-section",
      () => this.refresh(),
      true,
    );
    this.wantsSfw = new DynamicRadio(
      "wantsSfw",
      "#checklist-nsfw-section",
      () => this.refresh(),
      true,
    );
    this.$button = requireJQ("#checklist-dismiss-btn");
    this.$button.on("click", () => this.save());

    if (this.aasConfig.getIsFilled()) {
      this.illBeCareful.check();
      this.isAdult.selectVal(this.aasConfig.getIsAdult() ? "1" : "0");
      this.wantsSfw.selectVal(this.aasConfig.getWantsSfw() ? "1" : "0");
    }

    this.refresh();

    requireJQ("#checklist-container").removeClass("d-none");
  }

  public refresh(): void {
    this.isAdult.toggle(this.illBeCareful.isChecked);
    this.wantsSfw.toggle(
      this.illBeCareful.isChecked && this.isAdult.isVal("1"),
    );

    this.$button.prop("disabled", !this.isReady);
    this.$button.attr(
      "value",
      this.isReady
        ? "I will now click this button"
        : "I can't click this button yet",
    );
  }

  public get isReady(): boolean {
    return (
      this.illBeCareful.isChecked &&
      (this.isAdult.isVal("0") ||
        (this.isAdult.isVal("1") && this.wantsSfw.isAnySelected()))
    );
  }

  public save(): void {
    this.aasConfig.setIsAdult(this.isAdult.isVal("1"));
    this.aasConfig.setWantsSfw(!this.wantsSfw.isVal("0"));
    this.aasConfig.setIsFilled(true);
    this.aasConfig.save();
  }
}
