import SpecialValue from "./SpecialValue";
import Artisan from "../../../class/Artisan";

export default class TrackingIssuesValue extends SpecialValue {
    public static readonly VALUE: string = '!'; // grep-special-value-tracking-issues

    public constructor() {
        super(TrackingIssuesValue.VALUE);
    }

    public matches(artisan: Artisan): boolean {
        return this.selected && artisan.csTrackerIssue;
    }
}
