import AbstractSingleFieldUnFilter from './AbstractSingleFieldUnFilter';
import StatusWriter from '../StatusWriter';

export default class AnySetUnFilter<T> extends AbstractSingleFieldUnFilter<T> {
    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'any of', this.selectedLabels, this.isUnknownSelected() ? 'unknown' : undefined);
    }
}
