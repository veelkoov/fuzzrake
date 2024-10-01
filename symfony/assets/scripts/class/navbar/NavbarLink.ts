import NavbarElement from "./NavbarElement";
import type { MenuItem } from "./Menu";

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

  public constructor(
    node: HTMLAnchorElement,
    private readonly menuItem: MenuItem,
  ) {
    super(node);
    this.priority = getPriority(node.dataset["priority"]);
  }

  public get width(): number {
    return this.node.clientWidth;
  }

  public setVisible(visible: boolean): void {
    super.setVisible(visible);
    this.menuItem.setVisible(!visible);
  }
}

export default NavbarLink;
