import NavbarElement from "./NavbarElement";
import NavbarLink, { NavbarLinkPriority } from "./NavbarLink";
import NavbarOverflowMenuButton from "./NavbarOverflowMenuButton";
import Menu from "./Menu";

const ITEM_GAP_PX = 12;

const PRIORITY_NUMBER: Record<NavbarLinkPriority, number> = {
  high: 0,
  medium: 1,
  low: 2,
};

function parseMainChildren(children: NodeListOf<ChildNode>): {
  links: readonly HTMLAnchorElement[];
  separator: HTMLDivElement;
  overflowMenuButton: HTMLButtonElement;
} {
  const links: HTMLAnchorElement[] = [];
  let separator: HTMLDivElement | null = null;
  let overflowMenuButton: HTMLButtonElement | null = null;
  for (const child of children) {
    if (child instanceof Text && !child.textContent?.trim()) {
      continue;
    }

    if (
      child instanceof HTMLDivElement &&
      child.className === "nav-separator"
    ) {
      separator = child;
      continue;
    }

    if (
      child instanceof HTMLAnchorElement &&
      child.classList.contains("nav-link")
    ) {
      links.push(child);
      continue;
    }

    if (
      child instanceof HTMLButtonElement &&
      child.className === "nav-overflow-menu-button"
    ) {
      overflowMenuButton = child;
      continue;
    }

    // Prefer throwing an error over error supression. The reason for this is
    // that our algorithm expects to know how to calculate the size/layout of
    // the navbar. If we have extra elements in there we don't know about, our
    // calculation will be wrong and it'll be a bug.
    // We should always need to update our code here as we add/change items in
    // the navbar DOM.
    throw new Error("Unexpected child of navbar element");
  }

  if (!separator) {
    throw new Error("Could not find navbar separator");
  }

  if (!overflowMenuButton) {
    throw new Error("Could not find navbar overflow menu button");
  }

  return { separator, links, overflowMenuButton };
}

class Navbar {
  public static init(): Navbar {
    const root = document.getElementById("navbar");
    if (!root) {
      throw new Error("No navbar detected in DOM");
    }

    const main = root.querySelector("div.navbar-main");
    if (!(main instanceof HTMLDivElement)) {
      throw new Error("Cannot find navbar nav container");
    }

    const menuNode = root.querySelector("div.nav-menu");
    if (!(menuNode instanceof HTMLDivElement)) {
      throw new Error("Cannot find navbar menu node");
    }

    const menuBackdropNode = root.querySelector("div.nav-menu-backdrop");
    if (!(menuBackdropNode instanceof HTMLDivElement)) {
      throw new Error("Cannot find navbar menu backdrop");
    }

    return new Navbar(main, menuNode, menuBackdropNode);
  }

  private readonly resizeObserver: ResizeObserver;
  private readonly separator: NavbarElement<HTMLDivElement>;
  private readonly prioritizedLinks: readonly NavbarLink[];
  private readonly overflowMenuButton: NavbarOverflowMenuButton;
  private readonly menu: Menu;

  private constructor(
    main: HTMLDivElement,
    menuNode: HTMLDivElement,
    menuBackdropNode: HTMLDivElement,
  ) {
    const { separator, links, overflowMenuButton } = parseMainChildren(
      main.childNodes,
    );
    this.menu = new Menu(menuNode, menuBackdropNode);
    this.overflowMenuButton = new NavbarOverflowMenuButton(overflowMenuButton);
    this.overflowMenuButton.connect(this.menu);

    this.separator = new NavbarElement(separator);
    this.prioritizedLinks = links
      .map((node) => new NavbarLink(node, this.menu.cloneAppend(node)))
      .sort(
        (a, b) => PRIORITY_NUMBER[a.priority] - PRIORITY_NUMBER[b.priority],
      );

    this.resizeObserver = new ResizeObserver(([entry]): void =>
      this.layout(entry.contentRect.width),
    );
    this.resizeObserver.observe(main);
  }

  private layout(contentWidth: number): void {
    const { width: overflowMenuButtonWidth } = this.overflowMenuButton;

    // Figure out which items we have room for
    let remainingWidth = contentWidth;
    let visibleIndex = 0;
    while (remainingWidth > 0 && visibleIndex < this.prioritizedLinks.length) {
      // Figure out how much space is reserved on the navbar
      const reservedSize =
        visibleIndex === this.prioritizedLinks.length - 1
          ? // If this is the final link, then it means that if we show this link
            // we will also be making the separator between left and right sides
            // visible -- that is, we have 2 DOM nodes becoming visible, not just 1,
            // which means we have additional CSS `gap` to consider when measuring
            ITEM_GAP_PX
          : // If we don't know yet that we're able to fit ALL of the items on the navbar,
            // we'll make sure to reserve space for the overflow button. ONLY IF we're
            // looking at the final item (which, if it fits, means we don't need the
            // overflow button) do we omit the overflow button.
            overflowMenuButtonWidth + ITEM_GAP_PX;

      // If we can't fit the item, then we're finished
      if (
        remainingWidth <
        this.prioritizedLinks[visibleIndex].width + reservedSize
      ) {
        break;
      }

      // Include this link and check the next one
      remainingWidth -= this.prioritizedLinks[visibleIndex].width;
      remainingWidth -= ITEM_GAP_PX;
      visibleIndex++;
    }

    // Set links to be visible or invisible
    this.prioritizedLinks.forEach((link, index): void => {
      link.setVisible(index < visibleIndex);
    });

    // If all of our items are visible, then we have room to separate them into
    // different sides of the navbar. But if we're space constrained and we're
    // not able to display all of our items, we don't have enough room to separate
    // them out into different sides
    this.separator.setVisible(visibleIndex === this.prioritizedLinks.length);

    // If any navbar items aren't visible, then we'll show the overflow menu
    // button
    this.overflowMenuButton.setVisible(
      visibleIndex < this.prioritizedLinks.length,
    );
  }
}

export default Navbar;
