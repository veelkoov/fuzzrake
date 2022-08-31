export default class Radio implements RadioInterface {
    private readonly $elements: JQuery<HTMLElement>;

    constructor(
        private fieldName: string,
        private changeCallback: () => void,
    ) {
        this.$elements = jQuery(Radio.getSelector(fieldName));

        this.$elements.on('change', () => changeCallback());
    }

    public static getSelector(fieldName: string) {
        return `input[type=radio][name="${fieldName}"]`;
    }

    public val(): null|string {
        let $checked = this.$elements.filter(':checked');

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
        return this.$elements.index(this.$elements.filter(':checked'));
    }

    public selectVal(value: string): void {
        this.$elements.filter(idx => value === this.$elements.eq(idx).val())
            .prop('checked', true);
    }
}
