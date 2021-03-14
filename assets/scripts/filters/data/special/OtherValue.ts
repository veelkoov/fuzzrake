import SpecialValue from "./SpecialValue";
import Artisan from "../../../class/Artisan";
import AbstractUnknownValue from "./AbstractUnknownValue";

export default class OtherValue extends SpecialValue {
    public static readonly VALUE: string = '*';
    private readonly otherFieldName: string;

    public constructor(fieldName: string) {
        super(OtherValue.VALUE);

        this.otherFieldName = OtherValue.getOtherFieldName(fieldName);
    }

    public matches(artisan: Artisan): boolean {
        return this.selected && !AbstractUnknownValue.is(artisan[this.otherFieldName]);
    }

    public hasOtherValue(artisan: Artisan): boolean {
        return !AbstractUnknownValue.is(artisan[this.otherFieldName]);
    }

    public static getOtherFieldName(fieldName: string) {
        return 'other' + fieldName.charAt(0).toUpperCase() + fieldName.substr(1);
    }
}
