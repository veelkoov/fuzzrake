import error from "./ErrorMessage";

export function toggle(
  $elements: JQuery<HTMLElement> | string,
  visible: boolean | ((index: number, element: JQuery<HTMLElement>) => boolean),
  duration: JQuery.Duration = "fast",
): void {
  if (typeof $elements === "string") {
    $elements = jQuery($elements);
  }

  if (typeof visible === "boolean") {
    if (visible) {
      $elements.show(duration);
    } else {
      $elements.hide(duration);
    }
  } else {
    $elements.each((index, element) => {
      const $element = jQuery(element);

      toggle($element, visible(index, $element), duration);
    });
  }
}

export function singleStrValOrNull($element: JQuery): string | null {
  if (1 !== $element.length) {
    return null;
  }

  const value = $element.val();

  return undefined === value ? null : value.toString();
}

export function requireJQ(
  selector: string,
  min: number = 1,
  max: number | null = 1,
): JQuery<HTMLElement> {
  const result = jQuery(selector);

  if (result.length < min || (max !== null && result.length > max)) {
    error(
      "Failed matching HTML elements. Most probably this error happened because the maintainer introduced some changes on the website in a way which you would expect from an unskilled person.",
    )
      .withConsoleDetails(
        `Looked for: ${selector}, count: ${min}..${max}, matched: ${result.length}.`,
      )
      .reportOnce();
  }

  return result;
}
