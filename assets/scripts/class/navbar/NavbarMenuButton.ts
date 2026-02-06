import NavbarElement from "./NavbarElement";
import type Menu from "./Menu";

export type DisconnectFn = () => void;

class NavbarMenuButton extends NavbarElement<HTMLButtonElement> {
  private menu: Menu | null = null;

  public constructor(node: HTMLButtonElement) {
    super(node);
  }

  public get width(): number {
    return this.node.clientWidth;
  }

  public setVisible(visible: boolean): void {
    if (!this.menu && visible) {
      console.warn(
        "Cannot make a menu button visible if it isn't bound to the menu",
      );
      return;
    }

    super.setVisible(visible);
    if (!visible) {
      this.menu?.hide();
    }
  }

  public connect(menu: Menu): DisconnectFn {
    if (this.menu) {
      throw new Error(
        "NavbarMenuButton already connected to a menu, cannot reconnect",
      );
    }

    this.menu = menu;

    const clickHandler = (): void => {
      if (menu.isOpen) {
        menu.hide();
      } else {
        menu.show();
      }
    };
    this.node.addEventListener("click", clickHandler);

    const unsubscribeMenu = menu.subscribe((open): void => {
      this.node.classList.toggle("isOpen", open);
    });

    let hasUnbound = false;
    return (): void => {
      if (hasUnbound) {
        return;
      }

      hasUnbound = false;
      unsubscribeMenu();
      this.node.removeEventListener("click", clickHandler);
      this.node.classList.remove("isOpen");
      this.menu = null;
    };
  }
}

export default NavbarMenuButton;
