import Artisan from '../../../class/Artisan';
import SpecialValue from './SpecialValue';

export default abstract class AbstractUnknownValue extends SpecialValue {
    public static readonly VALUE: string = '?'; // grep-special-value-unknown

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
