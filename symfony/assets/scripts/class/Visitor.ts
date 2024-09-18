import Storage from "./Storage";

const STORAGE_KEY_LAST_VISITED_EVENTS_PAGE = "visitor/lastVisitedEventsPage";

class Visitor {
  public static get lastVisitedEventsPage(): Date | null {
    const timestamp = Storage.getString(
      STORAGE_KEY_LAST_VISITED_EVENTS_PAGE,
      "",
    );
    if (!timestamp) {
      return null;
    }

    // Ensure the string is actually a timestamp
    const date = new Date(timestamp);
    if (isNaN(date.valueOf())) {
      return null;
    }

    return date;
  }

  public static set lastVisitedEventsPage(date: Date | null) {
    if (!date) {
      Storage.remove(STORAGE_KEY_LAST_VISITED_EVENTS_PAGE);
      return;
    }

    Storage.saveString(
      STORAGE_KEY_LAST_VISITED_EVENTS_PAGE,
      date.toISOString(),
    );
  }
}

export default Visitor;
