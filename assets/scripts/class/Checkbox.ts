export class Checkbox {
    private $elems: JQuery<HTMLElement>;

    constructor(
        private id: string,
        private changeCallback: () => void,
    ) {
        this.$elems = jQuery(`#${id}`);

        this.$elems.on('change', () => changeCallback());
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
}
