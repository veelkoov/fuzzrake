import AbstractSingleFieldUnOtFilter from './AbstractSingleFieldUnOtFilter';
import Artisan from '../../class/Artisan';
import StatusWriter from '../StatusWriter';

export default class AllSetUnOtFilter<T> extends AbstractSingleFieldUnOtFilter<T> {
    public constructor(fieldName: string) {
        super(fieldName);
    }

    public matches(artisan: Artisan): boolean {
        if (!this.isActive() || this.matchesUnknown(artisan) || this.matchesOther(artisan)) {
            return true;
        }

        if ((this.isUnknownSelected() || this.isOtherSelected()) && 0 === this.selectedValues.size) {
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

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'all of', this.selectedLabels, this.isUnknownSelected() ? 'unknown' : undefined, this.isOtherSelected() ? 'Other' : undefined);
    }
}
