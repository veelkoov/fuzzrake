import AbstractUnknownValue from "./AbstractUnknownValue";
import Artisan from "../../../class/Artisan";

export default class UnknownValue extends AbstractUnknownValue {
    public constructor(protected readonly fieldName: string) {
        super();
    }

    public matches(artisan: Artisan): boolean {
        return this.selected && AbstractUnknownValue.is(artisan[this.fieldName]);
    }
}
