import { DateTime } from "luxon";

export const defaultDateTimeFormat: Intl.DateTimeFormatOptions = {
  year: "numeric",
  month: "short",
  day: "numeric",
  hour: "numeric",
  minute: "numeric",
};

export function localizeDateTimes(jQueryElements: JQuery): void {
  jQueryElements
    .find("span.utc_datetime")
    .each((_: number, htmlElement: HTMLElement): void => {
      const element = jQuery(htmlElement);

      const parts = element
        .text()
        .match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}) UTC$/); // grep-expected-utc-datetime-format

      if (null === parts) {
        return;
      }

      const isoDateTime = DateTime.fromISO(`${parts[1]}T${parts[2]}:00Z`);

      element.attr("title", element.text());
      element.html(isoDateTime.toLocal().toLocaleString(defaultDateTimeFormat));
    });
}
