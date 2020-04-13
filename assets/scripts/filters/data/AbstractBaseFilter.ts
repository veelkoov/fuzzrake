import FilterInterface from "./FilterInterface";
import Artisan from "../../class/Artisan";

export default abstract class AbstractBaseFilter implements FilterInterface {
    protected selectedValues: Set<string> = new Set<string>();

    public abstract matches(artisan: Artisan): boolean;

    public clear(): void {
        this.selectedValues.clear();
    }

    public getDataTableFilterCallback(artisans: Artisan[]): (_: any, __: any, index: number) => boolean {
        let _this: FilterInterface = this; // TODO: try without
        let _artisans: Artisan[] = artisans; // TODO: try without

        return function (_, __, index: number): boolean {
            return _this.matches(_artisans[index]);
        };
    }

    public isActive(): boolean {
        return this.selectedValues.size !== 0;
    }

    public deselect(value: string): void {
        this.selectedValues.delete(value);
    }

    public select(value: string): void {
        this.selectedValues.add(value);
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