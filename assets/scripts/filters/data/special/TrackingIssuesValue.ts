import SpecialValue from './SpecialValue';

export default class TrackingIssuesValue extends SpecialValue {
    public static readonly VALUE: string = '!'; // grep-special-value-tracking-issues

    public constructor() {
        super(TrackingIssuesValue.VALUE);
    }
}
