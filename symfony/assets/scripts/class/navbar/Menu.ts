type MenuListener = (isOpen: boolean) => void;
export type UnsubscribeFn = () => void;

type MenuMode = "dropdown" | "drawer";

export interface MenuItem {
  setVisible(visible: boolean): void;
}

class Menu {
  private readonly listeners = new Set<MenuListener>();

  public constructor(
    private readonly node: HTMLDivElement,
    private readonly backdropNode: HTMLDivElement,
  ) {
    backdropNode.addEventListener("click", (): void => {
      this.hide();
    });
  }

  public get isOpen(): boolean {
    return this.node.classList.contains("visible");
  }

  public get mode(): MenuMode {
    if (this.node.classList.contains("nav-dropdown-menu")) {
      return "dropdown";
    }

    if (this.node.classList.contains("nav-drawer-menu")) {
      return "drawer";
    }

    throw new Error("Unknown current nav menu mode");
  }

  public show(): void {
    if (this.isOpen) {
      return;
    }

    this.node.classList.toggle("visible", true);
    this.backdropNode.classList.toggle("visible", true);
    document.body.classList.toggle("nav-menu-open", true);
    this.listeners.forEach((listener) => listener(true));
  }

  public hide(): void {
    if (!this.isOpen) {
      return;
    }

    document.body.classList.toggle("nav-menu-open", false);
    this.backdropNode.classList.toggle("visible", false);
    this.node.classList.toggle("visible", false);
    this.listeners.forEach((listener) => listener(false));
  }

  public subscribe(listener: MenuListener): UnsubscribeFn {
    this.listeners.add(listener);
    return (): void => {
      this.listeners.delete(listener);
    };
  }

  public setMode(mode: MenuMode): void {
    if (this.mode === mode) {
      return;
    }

    this.node.classList.toggle("nav-dropdown-menu", mode === "dropdown");
    this.node.classList.toggle("nav-drawer-menu", mode === "drawer");
  }

  public cloneAppend(origItem: HTMLElement): MenuItem {
    const clone = origItem.cloneNode(true) as HTMLElement;
    this.node.appendChild(clone);
    return {
      setVisible: (visible): void => {
        clone.classList.toggle("hidden", !visible);
      },
    };
  }
}

export default Menu;
