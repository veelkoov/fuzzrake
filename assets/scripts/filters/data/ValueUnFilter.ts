import AbstractSingleFieldUnFilter from './AbstractSingleFieldUnFilter';
import StatusWriter from '../StatusWriter';

export default class ValueUnFilter<T> extends AbstractSingleFieldUnFilter<T> {
    public constructor(fieldName: string) {
        super(fieldName);
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'any of', this.selectedLabels, this.isUnknownSelected() ? 'unknown' : undefined);
    }
}
