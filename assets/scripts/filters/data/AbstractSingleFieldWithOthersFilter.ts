import AbstractSingleFieldFilter from "./AbstractSingleFieldFilter";
import Artisan from "../../class/Artisan";

export default abstract class AbstractSingleFieldWithOthersFilter extends AbstractSingleFieldFilter {
    private readonly otherFieldName: string;
    private readonly OTHER_VALUE: string = '*';
    private otherSelected: boolean = false;

    protected constructor(fieldName: string) {
        super(fieldName);
        this.otherFieldName = AbstractSingleFieldWithOthersFilter.getOtherFieldName(fieldName);
    }

    public isActive(): boolean {
        return this.otherSelected || super.isActive();
    }

    public select(value: string): void {
        if (value === this.OTHER_VALUE) {
            this.otherSelected = true;
        } else {
            super.select(value);
        }
    }

    public deselect(value: string): void {
        if (value === this.OTHER_VALUE) {
            this.otherSelected = false;
        } else {
            super.deselect(value);
        }
    }

    public clear(): void {
        super.clear();
        this.otherSelected = false;
    }

    protected matchesOther(artisan: Artisan): boolean {
        return this.otherSelected && this.hasOtherValue(artisan);
    }

    protected notMatchesOther(artisan: Artisan): boolean {
        return this.otherSelected && !this.hasOtherValue(artisan);
    }

    protected matchesUnknown(artisan: Artisan): boolean {
        return !this.hasOtherValue(artisan) && super.matchesUnknown(artisan);
    }

    private hasOtherValue(artisan: Artisan): boolean {
        return !this.isValueUnknown(artisan[this.otherFieldName]);
    }

    private static getOtherFieldName(fieldName: string) {
        return 'other' + fieldName.charAt(0).toUpperCase() + fieldName.substr(1);
    }
}
