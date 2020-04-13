import AbstractBaseFilter from "./AbstractBaseFilter";
import Artisan from "../../class/Artisan";

export default abstract class AbstractSingleFieldFilter extends AbstractBaseFilter {
    private readonly UNKNOWN_VALUE: string = '?';

    protected readonly fieldName: string;
    private unknownSelected: boolean = false;

    protected constructor(fieldName: string) {
        super();
        this.fieldName = fieldName;
    }

    protected matchesUnknown(artisan: Artisan): boolean {
        return this.unknownSelected && !this.isValueUnknown(artisan[this.fieldName]);
    }

    protected isValueUnknown(value: any): boolean {
        return value === null || value === '' || value instanceof Set && value.size === 0 || value instanceof Array && value.length === 0;
    }

    public clear(): void {
        super.clear();
        this.unknownSelected = false;
    }

    public isActive(): boolean {
        return this.unknownSelected || super.isActive();
    }

    public select(value: string): void {
        if (value === this.UNKNOWN_VALUE) {
            this.unknownSelected = true;
        } else {
            super.select(value);
        }
    }

    public deselect(value: string): void {
        if (value === this.UNKNOWN_VALUE) {
            this.unknownSelected = false;
        } else {
            super.deselect(value);
        }
    }
}
