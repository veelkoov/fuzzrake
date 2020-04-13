import FilterInterface from "./FilterInterface";
import Artisan from "../../class/Artisan";

export default abstract class AbstractBaseFilter<T> implements FilterInterface {
    protected selectedValues: Set<T> = new Set<T>();
    protected selectedLabels: Set<string> = new Set<string>();

    public abstract matches(artisan: Artisan): boolean;

    public clear(): void {
        this.selectedValues.clear();
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

    public abstract getStatus(): string;

    protected isValueUnknown(value: any): boolean {
        return value === null || value === '' || value instanceof Set && value.size === 0 || value instanceof Array && value.length === 0;
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
        return label.replace(/ \(.+?\)$/, '')
    }
}

// public restoreChoices(): void { // TODO
//     let values: string = localStorage[`filters/${this.fieldName}/choices`];
//
//     if (values) {
//         let valuesArr: string[] = values.split('\n');
//
//         this.$checkboxes.filter(
//             (index: number, element: HTMLElement) => valuesArr.includes(element.getAttribute('value'))
//         ).prop('checked', true);
//
//         this.updateSelection();
//     }
// }
//
// public saveChoices(): void { // TODO
//     try {
//         localStorage[`filters/${this.fieldName}/choices`] = this.selectedValues.join('\n');
//     } catch (e) {
//         // Not allowed? - I don't care then
//     }
// }