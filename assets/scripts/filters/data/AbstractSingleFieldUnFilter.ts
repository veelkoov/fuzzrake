import AbstractSingleFieldFilter from './AbstractSingleFieldFilter';
import UnknownValue from './special/UnknownValue';

export default abstract class AbstractSingleFieldUnFilter<T> extends AbstractSingleFieldFilter<T> {
    private readonly unknown: UnknownValue;

    public constructor(fieldName: string) {
        super(fieldName);
        this.unknown = new UnknownValue();
    }

    public clear(): void {
        super.clear();
        this.unknown.clear();
    }

    public isActive(): boolean {
        return this.unknown.isSelected() || super.isActive();
    }

    public isUnknownSelected(): boolean {
        return this.unknown.isSelected();
    }

    public select(value: string, label: string): void {
        this.unknown.select(value, label, () => {
            super.select(value, label);
        });
    }

    public deselect(value: string, label: string): void {
        this.unknown.deselect(value, label, () => {
            super.deselect(value, label);
        });
    }

    public isSelected(value: string): boolean {
        return this.unknown.checkSelected(value, () => {
            return super.isSelected(value);
        });
    }
}
