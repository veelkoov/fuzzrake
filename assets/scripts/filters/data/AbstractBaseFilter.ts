import FilterInterface from "./FilterInterface";
import Artisan from "../../class/Artisan";

export default abstract class AbstractBaseFilter<T> implements FilterInterface {
    protected selectedValues: Set<T> = new Set<T>();

    public abstract matches(artisan: Artisan): boolean;

    public clear(): void {
        this.selectedValues.clear();
    }

    public isActive(): boolean {
        return this.selectedValues.size !== 0;
    }

    public deselect(value: string): void {
        this.selectedValues.delete(this.mapValue(value));
    }

    public select(value: string): void {
        this.selectedValues.add(this.mapValue(value));
    }

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