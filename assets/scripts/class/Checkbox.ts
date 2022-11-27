export default class Checkbox {
    private $elems: JQuery<HTMLElement>;

    constructor(
        private id: string,
        private changeCallback: (Checkbox) => void,
    ) {
        this.$elems = jQuery(`#${id}`);

        this.$elems.on('change', () => changeCallback(this));
    }

    public val(): null|string {
        let $checked = this.$elems.filter(':checked');

        if (0 === $checked.length) {
            return null;
        }

        return $checked.val().toString();
    }

    public isChecked(): boolean {
        return null !== this.val();
    }

    public check(): void {
        this.$elems.prop('checked', true);
    }
}
