import ToggleableInterface from "./ToggleableInterface";
import { toggle } from "../../jQueryUtils";
import { ErrorMessage } from "../../ErrorMessage";

export default class DynamicFields implements ToggleableInterface {
  private readonly $fields: JQuery<HTMLElement>;
  private readonly $container: JQuery<HTMLElement>;
  private readonly required: boolean;
  private available = true; // Initial value assumed

  constructor(
    fieldsSelector: string,
    containerSelector: string,
    required: boolean | "from-html-attr",
  ) {
    this.$fields = jQuery(fieldsSelector);
    this.$container = jQuery(containerSelector);

    if (required !== "from-html-attr") {
      this.required = required;
    } else {
      if (this.$fields.length === 1) {
        this.required = this.$fields.prop("required");
      } else {
        new ErrorMessage("Unable to initialize form field.")
          .withConsoleDetails(
            `${fieldsSelector} length is ${this.$fields.length}, cannot set 'required' from the HTML attribute.`,
          )
          .reportOnce();

        this.required = true;
      }
    }
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
