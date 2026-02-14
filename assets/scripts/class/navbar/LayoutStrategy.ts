import type NavbarElement from "./NavbarElement";
import type NavbarLink from "./NavbarLink";
import NavbarMenuButton from "./NavbarMenuButton";
import type Menu from "./Menu";

export interface LayoutStrategyEvent {
  contentWidth: number;
  prioritizedLinks: readonly NavbarLink[];
  menu: Menu;
  overflowMenuButton: NavbarMenuButton;
  mobileDrawerButton: NavbarMenuButton;
  separator: NavbarElement<HTMLDivElement>;
}

interface LayoutStrategy {
  /**
   * Called when the navbar transitions to using this layout strategy.
   *
   * @guarantee Once called, this will not be called again on this
   * instance (future usages of this strategy will be new instances).
   * @guarantee Will always be called before {@link LayoutStrategy.update}.
   */
  init(event: LayoutStrategyEvent): void;

  /**
   * Called every time the width of the navbar changes.
   *
   * NOTE: During the initial frame that transitions to this strategy, this
   * will be called immediately after {@link LayoutStrategy.init}
   *
   * @guarantee Will always be called after {@link LayoutStrategy.init} has
   * been called.
   * @guarantee Will not be called after {@link LayoutStrategy.destroy} has
   * been called.
   */
  update(event: LayoutStrategyEvent): void;

  /**
   * Called when the navbar is transitioning to a new layout strategy.
   *
   * @guarantee Once called, this will not be called again on this
   * instance (future usages of this strategy will be new instances).
   */
  destroy(event: LayoutStrategyEvent): void;
}

export default LayoutStrategy;
