import ToggleableInterface from "./ToggleableInterface";
import { toggle } from "../../jQueryUtils";

export default class DynamicFields implements ToggleableInterface {
  private readonly $fields: JQuery<HTMLElement>;
  private readonly $container: JQuery<HTMLElement>;
  private available = true; // Initial value assumed

  constructor(
    fieldsSelector: string,
    containerSelector: string,
    private readonly required: boolean,
  ) {
    this.$fields = jQuery(fieldsSelector);
    this.$container = jQuery(containerSelector);
  }

  public toggle(available: boolean): void {
    this.available = available;

    this.$fields.prop("required", this.required && available);
    toggle(this.$container, available);
  }

  public isAvailable(): boolean {
    return this.available;
  }
}
