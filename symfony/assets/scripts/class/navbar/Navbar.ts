import DesktopLayoutStrategy from "./DesktopLayoutStrategy";
import LayoutStrategy, { LayoutStrategyEvent } from "./LayoutStrategy";
import MobileLayoutStrategy from "./MobileLayoutStrategy";
import NavbarElement from "./NavbarElement";
import NavbarLink, { NavbarLinkPriority } from "./NavbarLink";
import NavbarMenuButton from "./NavbarMenuButton";
import Menu, { MenuItem } from "./Menu";
import NewsNavbarLink from "./NewsNavbarLink";

const PRIORITY_NUMBER: Record<NavbarLinkPriority, number> = {
  high: 0,
  medium: 1,
  low: 2,
};

/**
 * Below this number, the navbar will display in the mobile version. Above this
 * number, the navbar will display in the desktop version.
 * Number was chosen to target conventional mobile devices as being "mobile" but
 * is otherwise arbitrary and can be changed as needed.
 */
const MOBILE_LAYOUT_BREAKPOINT_PX = 576;

function parseMainChildren(children: NodeListOf<ChildNode>): {
  links: readonly HTMLAnchorElement[];
  separator: HTMLDivElement;
  overflowMenuButton: HTMLButtonElement;
  mobileDrawerButton: HTMLButtonElement;
} {
  const links: HTMLAnchorElement[] = [];
  let separator: HTMLDivElement | null = null;
  let overflowMenuButton: HTMLButtonElement | null = null;
  let mobileDrawerButton: HTMLButtonElement | null = null;
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

    if (
      child instanceof HTMLButtonElement &&
      child.className === "nav-mobile-drawer-button"
    ) {
      mobileDrawerButton = child;
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

  if (!mobileDrawerButton) {
    throw new Error("Could not find navbar mobile drawer button");
  }

  return { separator, links, overflowMenuButton, mobileDrawerButton };
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

    // Menu starts out in "dropdown" mode when rendered in the HTML
    const menuNode = root.querySelector("div.nav-dropdown-menu");
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
  private readonly overflowMenuButton: NavbarMenuButton;
  private readonly mobileDrawerButton: NavbarMenuButton;
  private readonly menu: Menu;
  private activeLayoutStrategy: LayoutStrategy | null = null;

  private constructor(
    private readonly main: HTMLDivElement,
    menuNode: HTMLDivElement,
    menuBackdropNode: HTMLDivElement,
  ) {
    const { separator, links, overflowMenuButton, mobileDrawerButton } =
      parseMainChildren(main.childNodes);
    this.menu = new Menu(menuNode, menuBackdropNode);
    this.overflowMenuButton = new NavbarMenuButton(overflowMenuButton);
    this.mobileDrawerButton = new NavbarMenuButton(mobileDrawerButton);

    this.separator = new NavbarElement(separator);
    this.prioritizedLinks = links
      .map((node): NavbarLink => {
        let ConstructorForNavbarLink: new (
          node: HTMLAnchorElement,
          menuItem: MenuItem,
        ) => NavbarLink;

        switch (node.dataset["navitemType"]) {
          case "news": {
            ConstructorForNavbarLink = NewsNavbarLink;
            break;
          }
          case undefined: {
            ConstructorForNavbarLink = NavbarLink;
            break;
          }
          default: {
            throw new Error(
              `Unrecognized navbar link type '${node.dataset["navitemType"]}'`,
            );
          }
        }

        return new ConstructorForNavbarLink(node, this.menu.cloneAppend(node));
      })
      .sort(
        (a, b) => PRIORITY_NUMBER[a.priority] - PRIORITY_NUMBER[b.priority],
      );

    this.resizeObserver = new ResizeObserver(([entry]): void =>
      this.layout(entry.target.clientWidth, entry.contentRect.width),
    );
    this.resizeObserver.observe(main);
  }

  private layout(clientWidth: number, contentWidth: number): void {
    const event: LayoutStrategyEvent = {
      contentWidth,
      prioritizedLinks: this.prioritizedLinks,
      separator: this.separator,
      menu: this.menu,
      overflowMenuButton: this.overflowMenuButton,
      mobileDrawerButton: this.mobileDrawerButton,
    };

    // Figure out which layout strategy we *should* be using.
    // We want to use the `clientWidth` (total bounding box width) rather than
    // `contentWidth` (usable space inside node) since we want to include padding
    // and such (breakpoints are commonly measured in total width).
    const TargetLayoutStrategy =
      clientWidth <= MOBILE_LAYOUT_BREAKPOINT_PX
        ? MobileLayoutStrategy
        : DesktopLayoutStrategy;

    // If that isn't the strategy that we're currently using, let's transition
    // to it
    if (
      this.activeLayoutStrategy === null ||
      !(this.activeLayoutStrategy instanceof TargetLayoutStrategy)
    ) {
      this.activeLayoutStrategy?.destroy(event);
      this.activeLayoutStrategy = new TargetLayoutStrategy();
      this.activeLayoutStrategy.init(event);
    }

    // Have the guaranteed now-correct active strategy run its update
    this.activeLayoutStrategy.update(event);

    // If we haven't marked the main DOM node as being initialized already, do
    // so now that we've for-sure run our update at least once
    this.main.classList.add("initialized"); // Only appends if not present
  }
}

export default Navbar;
