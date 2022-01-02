export function toggle(
    $elements: JQuery<HTMLElement>,
    visible: boolean | ((index: number, element: JQuery<HTMLElement>) => boolean),
    duration: JQuery.Duration = 'fast',
): void {
    if (typeof visible === "boolean") {
        if (visible) {
            $elements.show(duration);
        } else {
            $elements.hide(duration);
        }
    } else {
        $elements.each((index, element) => {
            let $element = jQuery(element);

            toggle($element, visible(index, $element), duration);
        });
    }
}
