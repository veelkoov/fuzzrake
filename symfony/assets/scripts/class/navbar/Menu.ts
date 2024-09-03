type MenuListener = (isOpen: boolean) => void;

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

  public subscribe(listener: MenuListener): void {
    this.listeners.add(listener);
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
