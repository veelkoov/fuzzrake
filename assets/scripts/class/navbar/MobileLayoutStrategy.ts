import LayoutStrategy, { LayoutStrategyEvent } from "./LayoutStrategy";
import { DisconnectFn } from "./NavbarMenuButton";

class MobileLayoutStrategy implements LayoutStrategy {
  private disconnectDrawerMenuButton: DisconnectFn | null = null;

  public init({
    menu,
    mobileDrawerButton,
    overflowMenuButton,
    prioritizedLinks,
    separator,
  }: LayoutStrategyEvent): void {
    // Make all of the links, the separator, and the overflow menu button are
    // all invisible
    separator.setVisible(false);
    prioritizedLinks.forEach((link) => link.setVisible(false));
    overflowMenuButton.setVisible(false);

    // Connect the menu to the drawer menu button, and make sure it's visible
    this.disconnectDrawerMenuButton = mobileDrawerButton.connect(menu);
    mobileDrawerButton.setVisible(true);

    // If the overflow menu is already visible, let's close it for now
    // so we can reset things
    menu.hide();

    // Set the overflow menu into drawer mode
    menu.setMode("drawer");
  }

  public destroy({ menu }: LayoutStrategyEvent): void {
    // If the overflow menu is already visible, let's close it to reset
    // it
    menu.hide();

    // Disconnect the menu from the drawer button
    this.disconnectDrawerMenuButton?.();

    // Set the overflow menu back to dropdown mode (default)
    menu.setMode("dropdown");
  }

  public update(): void {}
}

export default MobileLayoutStrategy;
