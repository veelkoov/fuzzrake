import {toggle} from "../jQueryUtils";

export default class RequiredField {
    private $fields: JQuery<HTMLElement>;
    private $container: JQuery<HTMLElement>;

    constructor(fieldsSelector: string, containerSelector: string) {
        this.$fields = jQuery(fieldsSelector);
        this.$container = jQuery(containerSelector);
    }

    public setRequired(required: boolean): void {
        this.$fields.prop('required', required);
        toggle(this.$container, required);
    }
}
