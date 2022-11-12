import AbstractSingleFieldUnFilter from './AbstractSingleFieldUnFilter';
import OtherValue from './special/OtherValue';

export default abstract class AbstractSingleFieldUnOtFilter<T> extends AbstractSingleFieldUnFilter<T> {
    private readonly other: OtherValue

    public constructor(fieldName: string) {
        super(fieldName);
        this.other = new OtherValue(fieldName);
    }

    public isActive(): boolean {
        return this.other.isSelected() || super.isActive();
    }

    public isOtherSelected(): boolean {
        return this.other.isSelected();
    }

    public select(value: string, label: string): void {
        this.other.select(value, label, () => {
            super.select(value, label);
        });
    }

    public deselect(value: string, label: string): void {
        this.other.deselect(value, label, () => {
            super.deselect(value, label);
        });
    }

    public isSelected(value: string): boolean {
        return this.other.checkSelected(value, () => {
            return super.isSelected(value);
        });
    }

    public clear(): void {
        super.clear();
        this.other.clear();
    }
}
