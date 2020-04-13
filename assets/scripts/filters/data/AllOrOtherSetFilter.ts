import Artisan from "../../class/Artisan";
import AbstractSingleFieldWithOthersFilter from "./AbstractSingleFieldWithOthersFilter";

export default class AllOrOtherSetFilter<T> extends AbstractSingleFieldWithOthersFilter<T> {
    public constructor(fieldName: string) {
        super(fieldName);
    }

    public matches(artisan: Artisan): boolean {
        if (!this.isActive() || this.matchesUnknown(artisan)) {
            return true;
        }

        if (this.notMatchesOther(artisan)) {
            return false;
        }

        let target: Set<T> = artisan[this.fieldName];

        for (let value of this.selectedValues.values()) {
            if (!target.has(value)) {
                return false;
            }
        }

        return true;
    }
}
