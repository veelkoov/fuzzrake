import SpecialValue from "./SpecialValue";
import Artisan from "../../../class/Artisan";

export default abstract class AbstractUnknownValue extends SpecialValue {
    public static readonly VALUE: string = '?';

    protected constructor() {
        super(AbstractUnknownValue.VALUE);
    }

    public abstract matches(artisan: Artisan): boolean;

    public static is(value: any): boolean {
        return value === null || value === ''
            || value instanceof Set && value.size === 0
            || value instanceof Array && value.length === 0;
    }
}
