import Storage from "../class/Storage";
import { requireJQ } from "../jQueryUtils";

export default class ColumnsManager {
  private static readonly VISIBLE_BY_DEFAULT = [
    "styles",
    "commissions",
    "allergy-warning",
  ];
  private static readonly STORAGE_VERSION: string = "3";
  private static readonly UPDATE_EVENT: string = "change";

  private readonly classesBearer: JQuery<HTMLElement>;
  private readonly selectors: JQuery<HTMLElement>;

  constructor(classesBearerSelector: string, toggleLinksSelector: string) {
    this.classesBearer = requireJQ(classesBearerSelector);
    this.selectors = requireJQ(toggleLinksSelector, 1, null);

    this.loadOrUseDefaults();
    this.selectors.on(ColumnsManager.UPDATE_EVENT, (event) => {
      this.handleSelectorChange(jQuery(event.target));
      this.save();
    });
  }

  public save(): void {
    const state = this.selectors
      .filter((_, element) => this.isSelected(jQuery(element)))
      .map((_, element): string => this.getColumnId(jQuery(element)))
      .toArray()
      .join(",");

    Storage.saveString("columns/version", ColumnsManager.STORAGE_VERSION);
    Storage.saveString("columns/state", state);
  }

  public loadOrUseDefaults(): void {
    const missingValue = "59314295-b5d8-4c91-b6c7-ff2f71dd3e08"; // TODO: allow returning null instead of default
    const state: string = Storage.getString("columns/state", missingValue);
    let visibleColumnIds: string[];

    if (
      missingValue !== state &&
      ColumnsManager.STORAGE_VERSION ===
        Storage.getString("columns/version", "")
    ) {
      visibleColumnIds = state.split(",");
    } else {
      visibleColumnIds = ColumnsManager.VISIBLE_BY_DEFAULT;
    }

    this.selectors.each((_, element) => {
      const selector = jQuery(element);
      const columnId = this.getColumnId(selector);

      this.setSelected(selector, visibleColumnIds.includes(columnId));
      this.handleSelectorChange(selector);
    });
  }

  private handleSelectorChange(selector: JQuery<HTMLElement>): void {
    if (!this.isSelected(selector)) {
      this.hideColumn(selector);
    } else {
      this.showColumn(selector);
    }
  }

  private showColumn(selector: JQuery<HTMLElement>): void {
    this.classesBearer.addClass(`show-${this.getColumnId(selector)}`);
  }

  private hideColumn(selector: JQuery<HTMLElement>): void {
    this.classesBearer.removeClass(`show-${this.getColumnId(selector)}`);
  }

  private getColumnId(selector: JQuery<HTMLElement>): string {
    return selector.val()?.toString() || "";
  }

  private isSelected(selector: JQuery<HTMLElement>): boolean {
    return selector.is(":checked");
  }

  private setSelected(
    selector: JQuery<HTMLElement>,
    newSelected: boolean,
  ): void {
    selector.prop("checked", newSelected);
  }
}
