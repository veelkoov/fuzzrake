import {singleStrValOrNull} from '../jQueryUtils';

export default class Checkbox {
    private $elems: JQuery<HTMLElement>;

    constructor(
        private id: string,
        private changeCallback: (checkbox: Checkbox) => void,
    ) {
        this.$elems = jQuery(`#${id}`);

        this.$elems.on('change', () => changeCallback(this));
    }

    public val(): null | string {
        const $checked = this.$elems.filter(':checked');

        return singleStrValOrNull($checked);
    }

    public get isChecked(): boolean {
        return null !== this.val();
    }

    public check(): void {
        this.$elems.prop('checked', true);
    }
}
