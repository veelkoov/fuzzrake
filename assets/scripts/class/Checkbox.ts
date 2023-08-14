export default class Checkbox {
    private $elems: JQuery

    constructor(
        private id: string,
        private changeCallback: (checkbox: Checkbox) => void,
    ) {
        this.$elems = jQuery(`#${id}`);

        this.$elems.on('change', () => changeCallback(this));
    }

    public val(): null | string {
        const $checked = this.$elems.filter(':checked');

        if (0 === $checked.length) {
            return null;
        }

        const val = $checked.val();
        return undefined !== val ? val.toString() : null;
    }

    public isChecked(): boolean {
        return null !== this.val();
    }

    public check(): void {
        this.$elems.prop('checked', true);
    }
}
