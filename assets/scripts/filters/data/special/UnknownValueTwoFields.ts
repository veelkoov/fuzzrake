import AbstractUnknownValue from "./AbstractUnknownValue";
import Artisan from "../../../class/Artisan";

export default class UnknownValueTwoFields extends AbstractUnknownValue {
    public constructor(protected readonly fieldName1: string, protected readonly fieldName2: string) {
        super();
    }

    public matches(artisan: Artisan): boolean {
        return this.selected
            && AbstractUnknownValue.is(artisan[this.fieldName1])
            && AbstractUnknownValue.is(artisan[this.fieldName2]);
    }
}
