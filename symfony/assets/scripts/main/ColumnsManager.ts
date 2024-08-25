import Storage from "../class/Storage";
import { requireJQ } from "../jQueryUtils";

export default class ColumnsManager {
  private static readonly VISIBLE_BY_DEFAULT = [
    "styles",
    "commissions",
    "links",
  ];
  private static readonly STORAGE_VERSION: string = "2";

  private $classesBearer: JQuery<HTMLElement>;
  private $toggleLinks: JQuery<HTMLElement>;

  constructor(classesBearerSelector: string, toggleLinksSelector: string) {
    this.$classesBearer = requireJQ(classesBearerSelector);
    this.$toggleLinks = requireJQ(toggleLinksSelector, 1, null);

    this.loadOrUseDefaults();
    this.$toggleLinks.on("click", (event) =>
      this.handleVisibilityLinkClick(event),
    );
  }

  public save(): void {
    const state = this.$toggleLinks
      .filter(".active")
      .map((_, element): string => element.dataset["column-id"] || "")
      .toArray()
      .join(",");

    Storage.saveString("columns/version", ColumnsManager.STORAGE_VERSION);
    Storage.saveString("columns/state", state);
  }

  public loadOrUseDefaults(): void {
    const state: string = Storage.getString("columns/state", "");
    let visibleColumnIds: string[];

    if (
      "" !== state &&
      ColumnsManager.STORAGE_VERSION ===
        Storage.getString("columns/version", "")
    ) {
      visibleColumnIds = state.split(",");
    } else {
      visibleColumnIds = ColumnsManager.VISIBLE_BY_DEFAULT;
    }

    this.$toggleLinks.each((_, element) => {
      const $link = jQuery(element);
      const columnId = $link.data("column-id");

      if (visibleColumnIds.includes(columnId)) {
        this.showColumn($link);
      } else {
        this.hideColumn($link);
      }
    });
  }

  private handleVisibilityLinkClick(
    event: JQuery.ClickEvent<HTMLElement, undefined, HTMLElement, HTMLElement>,
  ): void {
    event.stopPropagation();

    const $link = jQuery(event.target);

    if ($link.hasClass("active")) {
      this.hideColumn($link);
    } else {
      this.showColumn($link);
    }

    this.save();
  }

  private showColumn($relatedVisibilityLink: JQuery<HTMLElement>): void {
    $relatedVisibilityLink.addClass("active");

    const columnId = $relatedVisibilityLink.data("column-id");
    this.$classesBearer.addClass(`show-${columnId}`);
  }

  private hideColumn($relatedVisibilityLink: JQuery<HTMLElement>): void {
    $relatedVisibilityLink.removeClass("active");

    const columnId = $relatedVisibilityLink.data("column-id");
    this.$classesBearer.removeClass(`show-${columnId}`);
  }
}
