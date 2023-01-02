import SpecialValue from './SpecialValue';

export default class UnknownValue extends SpecialValue {
    public static readonly VALUE: string = '?'; // grep-special-value-unknown

    public constructor() {
        super(UnknownValue.VALUE);
    }

    public static is(value: null|string|Array<string>): boolean {
        return value === null || value === '' || value instanceof Array && value.length === 0;
    }
}
