export default class Radio {
    private $elems: JQuery<HTMLElement>;

    constructor(
        private name: string,
        private changeCallback: () => void,
    ) {
        this.$elems = jQuery(`input[type=radio][name="${name}"]`);

        this.$elems.on('change', () => changeCallback());
    }

    public val(): null|string {
        let $checked = this.$elems.filter(':checked');

        if (0 === $checked.length) {
            return null;
        }

        return $checked.val().toString();
    }

    public isVal(value: string): boolean {
        return this.val() === value;
    }

    public isAnySelected(): boolean {
        return null !== this.val();
    }

    public selectedIdx(): number {
        return this.$elems.index(this.$elems.filter(':checked'));
    }

    public selectVal(value: string): void {
        this.$elems.filter(idx => value === this.$elems.eq(idx).val())
            .prop('checked', true);
    }
}
