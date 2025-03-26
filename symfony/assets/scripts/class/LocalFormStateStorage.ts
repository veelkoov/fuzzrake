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
    Storage.saveString(this.dataKey, JSON.stringify(data));
    Storage.saveString(this.dateTimeKey, this.getCurrentDateTime());
  }

  public getSaveDateTime(): string {
    return Storage.getString(this.dateTimeKey, this.getCurrentDateTime());
  }

  public getSavedState(): FieldsStates {
    return JSON.parse(Storage.getString(this.dataKey, "{}"));
  }

  private getCurrentDateTime(): string {
    return moment().format("YYYY-MM-DD HH:mm");
  }
}
