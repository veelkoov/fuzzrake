import NavbarElement from "./NavbarElement";
import type { MenuItem } from "./Menu";
import Menu from "./Menu";

export type NavbarLinkPriority = "high" | "medium" | "low";

function getPriority(str: string | undefined): NavbarLinkPriority {
  const lowered = str?.toLowerCase();
  switch (lowered) {
    case "high":
    case "medium":
    case "low": {
      return lowered;
    }
    default: {
      return "medium";
    }
  }
}

class NavbarLink extends NavbarElement<HTMLAnchorElement> {
  public readonly priority: NavbarLinkPriority;
  private menuItem: MenuItem | null = null;

  public constructor(node: HTMLAnchorElement) {
    super(node);
    this.priority = getPriority(node.dataset["priority"]);
  }

  protected get iconEl(): HTMLElement {
    for (const el of this.node.childNodes) {
      if (el instanceof HTMLElement && el.nodeName === "I") {
        return el;
      }
    }

    throw new Error("Could not find icon element");
  }

  public get width(): number {
    return this.node.clientWidth;
  }

  public setVisible(visible: boolean): void {
    super.setVisible(visible);
    this.menuItem?.setVisible(!visible);
  }

  public addToMenu(menu: Menu): void {
    if (this.menuItem) {
      throw new Error("NavbarLink already attached to a menu");
    }

    this.menuItem = menu.cloneAppend(this.node);
  }
}

export default NavbarLink;
