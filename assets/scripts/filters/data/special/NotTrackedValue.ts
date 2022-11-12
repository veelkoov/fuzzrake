import SpecialValue from './SpecialValue';

export default class NotTrackedValue extends SpecialValue {
    public static readonly VALUE: string = '-'; // grep-special-value-not-tracked

    public constructor() {
        super(NotTrackedValue.VALUE);
    }
}
