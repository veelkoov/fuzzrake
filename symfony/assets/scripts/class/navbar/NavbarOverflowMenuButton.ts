import NavbarElement from "./NavbarElement";
import type Menu from "./Menu";

class NavbarOverflowMenuButton extends NavbarElement<HTMLButtonElement> {
  private menu: Menu | null = null;

  public constructor(node: HTMLButtonElement) {
    super(node);
  }

  public get width(): number {
    return this.node.clientWidth;
  }

  public setVisible(visible: boolean): void {
    super.setVisible(visible);
    if (!visible) {
      this.menu?.hide();
    }
  }

  public connect(menu: Menu): void {
    if (menu === this.menu) {
      return;
    }

    this.menu = menu;
    this.node.addEventListener("click", (): void => {
      if (menu.isOpen) {
        menu.hide();
      } else {
        menu.show();
      }
    });
    menu.subscribe((open): void => {
      this.node.classList.toggle("isOpen", open);
    });
  }
}

export default NavbarOverflowMenuButton;
