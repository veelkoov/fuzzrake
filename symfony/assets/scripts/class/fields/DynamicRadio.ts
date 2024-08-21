import DynamicFields from "./DynamicFields";
import Radio from "./Radio";
import RadioInterface from "./RadioInterface";
import ToggleableInterface from "./ToggleableInterface";

export default class DynamicRadio
  implements RadioInterface, ToggleableInterface
{
  private radio: Radio;
  private fields: DynamicFields;

  constructor(
    fieldName: string,
    containerSelector: string,
    changeCallback: () => void,
    required: boolean,
  ) {
    this.radio = new Radio(fieldName, changeCallback);
    this.fields = new DynamicFields(
      Radio.getSelector(fieldName),
      containerSelector,
      required,
    );
  }

  public isAvailable(): boolean {
    return this.fields.isAvailable();
  }

  public isAnySelected(): boolean {
    return this.isAvailable() && this.radio.isAnySelected();
  }

  public isVal(value: string): boolean {
    return this.isAvailable() && this.radio.isVal(value);
  }

  public selectedIdx(): number {
    return this.isAvailable() ? this.radio.selectedIdx() : -1;
  }

  public toggle(available: boolean): void {
    this.fields.toggle(available);
  }

  public val(): string | null {
    return this.isAvailable() ? this.radio.val() : null;
  }
}
