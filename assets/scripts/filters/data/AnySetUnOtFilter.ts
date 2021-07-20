import AbstractSingleFieldUnOtFilter from "./AbstractSingleFieldUnOtFilter";
import Artisan from "../../class/Artisan";
import StatusWriter from "../StatusWriter";

export default class AnySetUnOtFilter<T> extends AbstractSingleFieldUnOtFilter<T> {
    public constructor(fieldName: string) {
        super(fieldName);
    }

    public matches(artisan: Artisan): boolean {
        if (!this.isActive() || this.matchesUnknown(artisan) || this.matchesOther(artisan)) {
            return true;
        }

        let target: Set<T> = artisan[this.fieldName];

        for (let value of this.selectedValues.values()) {
            if (target.has(value)) {
                return true;
            }
        }

        return false;
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'any of', this.selectedLabels, this.isUnknownSelected() ? 'unknown' : undefined, this.isOtherSelected() ? 'Other' : undefined);
    }
}
