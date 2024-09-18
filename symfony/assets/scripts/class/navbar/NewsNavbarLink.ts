import NavbarLink from "./NavbarLink";
import type { MenuItem } from "./Menu";
import Visitor from "../Visitor";

function createUnreadBadge(): HTMLDivElement {
  const badge = document.createElement("div");
  badge.classList.add("unread-badge");
  return badge;
}

class NewsNavbarLink extends NavbarLink {
  public constructor(node: HTMLAnchorElement, menuItem: MenuItem) {
    super(node, menuItem);

    if (this.shouldShowBadge) {
      this.iconEl.appendChild(createUnreadBadge());
    }
  }

  private get shouldShowBadge(): boolean {
    // If we're ON the events page, then never show a badge
    if (this.node.classList.contains("active")) {
      return false;
    }

    // Check to see if we have a valid timestamp for when events were last updated
    const timestampStr = this.node.dataset["latestEvent"];
    if (!timestampStr) {
      return false;
    }

    const timestamp = new Date(timestampStr);
    if (isNaN(timestamp.valueOf())) {
      return false;
    }

    // If the user has never visited the events page, don't show the badge.
    // It would be annoying to show up to a new website for the first time and be
    // told you have unread notifications.
    const { lastVisitedEventsPage } = Visitor;
    if (!lastVisitedEventsPage) {
      return false;
    }

    // If there've been no new events since they last visited, then don't display
    // the badge
    if (lastVisitedEventsPage.valueOf() >= timestamp.valueOf()) {
      return false;
    }

    return true;
  }
}

export default NewsNavbarLink;
