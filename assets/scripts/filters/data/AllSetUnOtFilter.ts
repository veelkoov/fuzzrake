import AbstractSingleFieldUnOtFilter from './AbstractSingleFieldUnOtFilter';
import StatusWriter from '../StatusWriter';

export default class AllSetUnOtFilter<T> extends AbstractSingleFieldUnOtFilter<T> {
    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'all of', this.selectedLabels, this.isUnknownSelected() ? 'unknown' : undefined, this.isOtherSelected() ? 'Other' : undefined);
    }
}
