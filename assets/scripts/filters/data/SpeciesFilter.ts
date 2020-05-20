import AbstractSingleFieldFilter from "./AbstractSingleFieldFilter";
import Artisan from "../../class/Artisan";
import StatusWriter from "../StatusWriter";
import AbstractBaseFilter from "./AbstractBaseFilter";

export default class ValueFilter<T> extends AbstractBaseFilter<T> {
    public constructor(fieldNameIn: string, fieldNameOut: string) {
        super();
    }

    public matches(artisan: Artisan): boolean {
        if (!this.isActive()) {
            return true;
        } // TODO

        // let target: T = artisan[this.fieldName];
        //
        // for (let value of this.selectedValues.values()) {
        //     if (target === value) {
        //         return true;
        //     }
        // }

        return false;
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), false, 'any of', this.selectedLabels); // TODO: unknown, others?
    }

    getStorageName(): string {
        return "";
    }
}
