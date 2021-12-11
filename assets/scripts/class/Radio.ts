export class Radio {
    private $elems: JQuery<HTMLElement>;

    constructor(
        private name: string,
        private changeCallback: () => void,
    ) {
        this.$elems = jQuery(`input[type=radio][name="${name}"]`);

        this.$elems.on('change', this.changeCallback);
    }

    public val(): string|number|string[] {
        return this.$elems.filter(':checked').val();
    }

    public isVal(value: string): boolean {
        return this.val() === value;
    }
}
