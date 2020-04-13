import AbstractBaseFilter from "./AbstractBaseFilter";
import Artisan from "../../class/Artisan";

export default abstract class AbstractSingleFieldFilter<T> extends AbstractBaseFilter<T> {
    private readonly UNKNOWN_VALUE: string = '?';

    protected readonly fieldName: string;
    private unknownSelected: boolean = false;

    protected constructor(fieldName: string) {
        super();
        this.fieldName = fieldName;
    }

    public getStorageName(): string {
        return this.fieldName;
    }

    protected matchesUnknown(artisan: Artisan): boolean {
        return this.unknownSelected && this.isValueUnknown(artisan[this.fieldName]);
    }

    public clear(): void {
        super.clear();
        this.unknownSelected = false;
    }

    public isActive(): boolean {
        return this.unknownSelected || super.isActive();
    }

    protected isUnknownSelected(): boolean {
        return this.unknownSelected;
    }

    public select(value: string, label: string): void {
        if (value === this.UNKNOWN_VALUE) {
            this.unknownSelected = true;
        } else {
            super.select(value, label);
        }
    }

    public deselect(value: string, label: string): void {
        if (value === this.UNKNOWN_VALUE) {
            this.unknownSelected = false;
        } else {
            super.deselect(value, label);
        }
    }

    public isSelected(value: string): boolean {
        if (value === this.UNKNOWN_VALUE) {
            return this.unknownSelected;
        } else {
            return super.isSelected(value);
        }
    }
}
