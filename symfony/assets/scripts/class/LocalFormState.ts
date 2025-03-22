import { unique } from "../arrayUtils";
import DarnIt from "../DarnIt";
import Storage from "./Storage";

type FieldPartState = { value: string; checked: boolean | null };
type FieldPartsStates = Array<FieldPartState>;
type FieldsStates = { [key: string]: FieldPartsStates };

export default class LocalFormState {
  private readonly fields: Map<string, JQuery> = new Map();
  private readonly storagePrefix: string;

  static setup(formName: string) {
    new this(formName);
  }

  constructor(private readonly formName: string) {
    this.storagePrefix = `savedFormStates/${this.formName}`; // TODO: Record time of save

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

    Storage.saveString(this.storagePrefix, JSON.stringify(data));
  }

  private restoreFieldsState(): void {
    const data: FieldsStates = JSON.parse(
      Storage.getString(this.storagePrefix, "{}"),
    );

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
