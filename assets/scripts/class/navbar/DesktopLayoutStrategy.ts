import LayoutStrategy, { LayoutStrategyEvent } from "./LayoutStrategy";
import { DisconnectFn } from "./NavbarMenuButton";

const ITEM_GAP_PX = 12;

class DesktopLayoutStrategy implements LayoutStrategy {
  private disconnectOverflowMenuButton: DisconnectFn | null = null;

  public init({
    menu,
    mobileDrawerButton,
    overflowMenuButton,
  }: LayoutStrategyEvent): void {
    // The drawer menu button will never be visible in this layout
    mobileDrawerButton.setVisible(false);

    // Connect the menu to the overflow button
    this.disconnectOverflowMenuButton = overflowMenuButton.connect(menu);
  }

  public destroy(): void {
    // Disconnect the menu from the overflow button
    this.disconnectOverflowMenuButton?.();
  }

  public update({
    contentWidth,
    prioritizedLinks,
    separator,
    overflowMenuButton,
  }: LayoutStrategyEvent): void {
    const { width: overflowMenuButtonWidth } = overflowMenuButton;

    // Figure out which items we have room for
    let remainingWidth = contentWidth;
    let visibleIndex = 0;
    while (remainingWidth > 0 && visibleIndex < prioritizedLinks.length) {
      // Figure out how much space is reserved on the navbar
      const reservedSize =
        visibleIndex === prioritizedLinks.length - 1
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
        prioritizedLinks[visibleIndex].width + reservedSize
      ) {
        break;
      }

      // Include this link and check the next one
      remainingWidth -= prioritizedLinks[visibleIndex].width;
      remainingWidth -= ITEM_GAP_PX;
      visibleIndex++;
    }

    // Set links to be visible or invisible
    prioritizedLinks.forEach((link, index): void => {
      link.setVisible(index < visibleIndex);
    });

    // If all of our items are visible, then we have room to separate them into
    // different sides of the navbar. But if we're space constrained and we're
    // not able to display all of our items, we don't have enough room to separate
    // them out into different sides
    separator.setVisible(visibleIndex === prioritizedLinks.length);

    // If any navbar items aren't visible, then we'll show the overflow menu
    // button
    overflowMenuButton.setVisible(visibleIndex < prioritizedLinks.length);
  }
}

export default DesktopLayoutStrategy;
