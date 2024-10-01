class NavbarElement<TNode extends HTMLElement> {
  public constructor(protected readonly node: TNode) {}

  public setVisible(visible: boolean): void {
    this.node.classList.toggle("hidden", !visible);
  }
}

export default NavbarElement;
