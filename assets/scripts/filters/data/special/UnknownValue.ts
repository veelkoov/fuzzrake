import SpecialValue from './SpecialValue';

export default class UnknownValue extends SpecialValue {
    public static readonly VALUE: string = '?'; // grep-special-value-unknown

    public constructor() {
        super(UnknownValue.VALUE);
    }

    public static is(value: any): boolean {
        return value === null || value === ''
            || value instanceof Set && value.size === 0
            || value instanceof Array && value.length === 0;
    }
}
