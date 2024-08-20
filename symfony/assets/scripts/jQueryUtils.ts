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
