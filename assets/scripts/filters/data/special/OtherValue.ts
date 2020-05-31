import SpecialValue from "./SpecialValue";
import Artisan from "../../../class/Artisan";
import UnknownValue from "./UnknownValue";

export default class OtherValue extends SpecialValue {
    public static readonly VALUE: string = '*';

    public constructor(otherFieldName: string) {
        super(OtherValue.VALUE, otherFieldName);
    }

    public matches(artisan: Artisan): boolean {
        return this.selected && !UnknownValue.is(artisan[this.fieldName]);
    }

    public hasOtherValue(artisan: Artisan): boolean {
        return !UnknownValue.is(artisan[this.fieldName]);
    }
}
