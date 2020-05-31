import SpecialValue from "./SpecialValue";
import Artisan from "../../../class/Artisan";

export default class UnknownValue extends SpecialValue {
    public static readonly VALUE: string = '?';

    public constructor(fieldName: string) {
        super(UnknownValue.VALUE, fieldName);
    }

    public matches(artisan: Artisan): boolean {
        return this.selected && UnknownValue.is(artisan[this.fieldName]);
    }

    public static is(value: any): boolean {
        return value === null || value === ''
            || value instanceof Set && value.size === 0
            || value instanceof Array && value.length === 0;
    }
}
