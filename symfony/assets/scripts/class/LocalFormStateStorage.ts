import Storage from "./Storage";
import { FieldsStates } from "./LocalFormStateTypes";
import * as moment from "moment/moment";

export default class LocalFormStateStorage {
  private readonly dataKey: string;
  private readonly dateTimeKey: string;

  constructor(formName: string, instanceId: string) {
    const storagePrefix = `savedFormStates/${formName}/${instanceId}`;

    this.dataKey = `${storagePrefix}/data`;
    this.dateTimeKey = `${storagePrefix}/saveDateTime`;
  }

  public reset(): void {
    Storage.remove(this.dataKey);
    Storage.remove(this.dateTimeKey);
  }

  public saveState(data: FieldsStates): void {
    // Ignore the potential situation when one of below saved information
    // already expired, but the other did not. Too unlikely.
    const twoMonthsInSeconds = 60 * 60 * 24 * 62;

    Storage.saveString(
      this.dataKey,
      JSON.stringify(data, null, 2),
      twoMonthsInSeconds,
    );

    if (!Storage.has(this.dateTimeKey)) {
      Storage.saveString(
        this.dateTimeKey,
        this.getCurrentDateTime(),
        twoMonthsInSeconds,
      );
    }
  }

  public getSaveDateTime(): string {
    return Storage.getString(this.dateTimeKey, this.getCurrentDateTime());
  }

  public getSavedState(): FieldsStates {
    return JSON.parse(this.getRawSavedState());
  }

  public getRawSavedState(): string {
    let result = Storage.getString(this.dataKey, "{}");

    // TODO: LEGACY: Remove after 2025-06
    result = result.replace(
      '"iu_form[emailAddressObfuscated]"',
      '"iu_form[emailAddress]"',
    );

    return result;
  }

  private getCurrentDateTime(): string {
    return moment().format("YYYY-MM-DD HH:mm");
  }
}
