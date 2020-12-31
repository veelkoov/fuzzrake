import AbstractBaseFilter from "./AbstractBaseFilter";
import Artisan from "../../class/Artisan";
import UnknownValue from "./special/UnknownValue";

export default abstract class AbstractSingleFieldFilter<T> extends AbstractBaseFilter<T> {
    protected readonly fieldName: string;
    private readonly unknown: UnknownValue;

    protected constructor(fieldName: string) {
        super();
        this.fieldName = fieldName;
        this.unknown = new UnknownValue(fieldName);
    }

    public getStorageName(): string {
        return this.fieldName;
    }

    protected matchesUnknown(artisan: Artisan): boolean {
        return this.unknown.matches(artisan);
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
