import AbstractSingleFieldUnFilter from "./AbstractSingleFieldUnFilter";
import Artisan from "../../class/Artisan";
import StatusWriter from "../StatusWriter";

export default class ValueUnFilter<T> extends AbstractSingleFieldUnFilter<T> {
    public constructor(fieldName: string) {
        super(fieldName);
    }

    public matches(artisan: Artisan): boolean {
        if (!this.isActive() || this.matchesUnknown(artisan)) {
            return true;
        }

        let target: T = artisan[this.fieldName];

        for (let value of this.selectedValues.values()) {
            if (target === value) {
                return true;
            }
        }

        return false;
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'any of', this.selectedLabels, this.isUnknownSelected() ? 'unknown' : undefined);
    }
}
