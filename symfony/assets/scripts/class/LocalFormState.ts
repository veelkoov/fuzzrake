import { unique } from "../arrayUtils";
import DarnIt from "../DarnIt";
import LocalFormStateStorage from "./LocalFormStateStorage";
import { FieldPartsStates, FieldsStates } from "./LocalFormStateTypes";

export default class LocalFormState {
  private readonly fields: Map<string, JQuery> = new Map();
  private readonly storage: LocalFormStateStorage;

  constructor(formName: string, instanceId: string) {
    this.storage = new LocalFormStateStorage(formName, instanceId);

    const allFields = LocalFormState.getTrackedFields(formName);

    unique(
      allFields.map((_, element) => jQuery(element).attr("name") ?? ""),
    ).forEach((fieldsName) =>
      this.fields.set(
        fieldsName,
        allFields.filter(
          (_: number, htmlElement: HTMLElement) =>
            jQuery(htmlElement).attr("name") === fieldsName,
        ),
      ),
    );

    this.restoreFieldsState();

    allFields.on("change", () => this.saveState());
  }

  public getSaveDateTime(): string {
    return this.storage.getSaveDateTime();
  }

  public reset(): void {
    this.storage.reset();
  }

  private saveState(): void {
    const data: FieldsStates = {};

    this.fields.forEach((field: JQuery, fieldName: string) => {
      const fieldPartsStates: FieldPartsStates = [];

      field.each((index) => {
        const fieldPart = field.eq(index);

        const value = fieldPart.val();
        if ("string" !== typeof value) {
          DarnIt.report(
            "Failed to preserve form state: " + typeof value,
            null,
            true,
          ); // TODO: Better message
          return;
        }

        const checkedAny = fieldPart
          .filter('input[type="radio"], input[type="checkbox"]')
          .prop("checked");

        fieldPartsStates.push({
          value: value,
          checked: "boolean" === typeof checkedAny ? checkedAny : null,
        });
      });

      data[fieldName] = fieldPartsStates;
    });

    this.storage.saveState(data);
  }

  // TODO: There were some issues while handling the information you entered. It is possible that once submitted, some of it may be lost. Try to finish sending the form, but even if you succeed, please note the time of seeing this message and contact the website maintainer. I am terribly sorry for the inconvenience!
  private restoreFieldsState(): void {
    const data: FieldsStates = this.storage.getSavedState();

    for (const fieldName in data) {
      const field = this.fields.get(fieldName);

      if (!field) {
        DarnIt.report("Failed to restore form state: " + fieldName, null, true); // TODO: Better message
        return;
      }

      const fieldPartStates: FieldPartsStates = data[fieldName];

      fieldPartStates.forEach((fieldPartState) => {
        if (null === fieldPartState.checked) {
          if (fieldPartStates.length !== 1) {
            DarnIt.report(
              "Failed to restore form state: " + JSON.stringify(fieldPartState),
              null,
              true,
            ); // TODO: Better message
            return;
          }

          field.val(fieldPartState.value).trigger("change");
        } else {
          const fieldPart = field.filter(
            (_, element) => jQuery(element).val() === fieldPartState.value,
          );

          if (fieldPart.length !== 1) {
            DarnIt.report(
              "Failed to restore form state: " + JSON.stringify(fieldPartState),
              null,
              true,
            ); // TODO: Better message
            return;
          }

          fieldPart.prop("checked", fieldPartState.checked).trigger("change");
        }
      });
    }
  }

  private static getTrackedFields(formName: string): JQuery {
    return jQuery(`*[name^='${formName}[']`)
      .filter("input, select, textarea")
      .filter((_, htmlElement) => {
        return !["password", "hidden"].includes(
          jQuery(htmlElement).attr("type")?.toLowerCase() ?? "",
        );
      });
  }
}
