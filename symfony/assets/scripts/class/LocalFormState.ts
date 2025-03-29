import { unique } from "../arrayUtils";
import LocalFormStateStorage from "./LocalFormStateStorage";
import { FieldPartsStates, FieldsStates } from "./LocalFormStateTypes";

export default class LocalFormState {
  private readonly fields: Map<string, JQuery> = new Map();
  private readonly storage: LocalFormStateStorage;

  constructor(formName: string, instanceId: string) {
    this.storage = LocalFormState.getStorage(formName, instanceId);
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

  private static getStorage(
    formName: string,
    instanceId: string,
  ): LocalFormStateStorage {
    return new LocalFormStateStorage(formName, instanceId);
  }

  public getSaveDateTime(): string {
    return this.storage.getSaveDateTime();
  }

  public reset(): void {
    this.storage.reset();
  }

  // TODO: Handle exceptions
  private saveState(): void {
    const data: FieldsStates = {};

    this.fields.forEach((field: JQuery, fieldName: string) => {
      const fieldPartsStates: FieldPartsStates = [];

      field.each((index) => {
        const fieldPart = field.eq(index);

        const value = fieldPart.val();
        if ("string" !== typeof value) {
          throw new TypeError(`Value of '${fieldName}' is not string.`);
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

  // TODO: Handle exceptions
  private restoreFieldsState(): void {
    const data: FieldsStates = this.storage.getSavedState();

    for (const fieldName in data) {
      const field = this.fields.get(fieldName);

      if (!field) {
        throw new TypeError(`Field '${fieldName}' does not exist.`);
      }

      const fieldPartStates: FieldPartsStates = data[fieldName];

      fieldPartStates.forEach((fieldPartState) => {
        if (null === fieldPartState.checked) {
          if (fieldPartStates.length !== 1) {
            throw new TypeError(`Field '${fieldName}' had multiple values.`);
          }

          field.val(fieldPartState.value).trigger("change");
        } else {
          const fieldPart = field.filter(
            (_, element) => jQuery(element).val() === fieldPartState.value,
          );

          if (fieldPart.length !== 1) {
            throw new TypeError(
              `${fieldPart.length} field '${fieldName}' parts matched value: '{fieldPartState.value}'.`,
            );
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

  public static cleanup(formName: string, instanceId: string): void {
    this.getStorage(formName, instanceId).reset();
  }
}
