export function toggle($elements: JQuery<HTMLElement>, visible: boolean, duration: JQuery.Duration = 'fast'): void {
    if (visible) {
        $elements.show(duration);
    } else
        $elements.hide(duration);
}
