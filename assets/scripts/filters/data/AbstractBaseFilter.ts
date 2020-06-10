import FilterInterface from "./FilterInterface";
import Artisan from "../../class/Artisan";

export default abstract class AbstractBaseFilter<T> implements FilterInterface {
    protected selectedValues: Set<T> = new Set<T>();
    protected selectedLabels: Set<string> = new Set<string>();

    public abstract matches(artisan: Artisan): boolean;

    public abstract getStorageName(): string;

    public abstract getStatus(): string;

    public clear(): void {
        this.selectedValues.clear();
        this.selectedLabels.clear();
    }

    public isActive(): boolean {
        return this.selectedValues.size !== 0;
    }

    public setSelected(isSelected: boolean, value: string, label: string): void {
        if (isSelected) {
            this.select(value, label);
        } else {
            this.deselect(value, label);
        }
    }

    public deselect(value: string, label: string): void {
        this.selectedValues.delete(this.mapValue(value));
        this.selectedLabels.delete(AbstractBaseFilter.fixLabel(label));
    }

    public select(value: string, label: string): void {
        this.selectedValues.add(this.mapValue(value));
        this.selectedLabels.add(AbstractBaseFilter.fixLabel(label));
    }

    public isSelected(value: string): boolean {
        return this.selectedValues.has(this.mapValue(value));
    }

    private mapValue(value: string): T {
        if (value === '0') {
            return <T><unknown>false;
        } else if (value === '1') {
            return <T><unknown>true;
        } else {
            return <T><unknown>value;
        }
    }

    private static fixLabel(label: string): string {
        return label.replace(/ \(.+?\)$/, '') // FIXME: Need to get rid of this when explanation of items won't be done by appending it to the name of the item
    }
}
