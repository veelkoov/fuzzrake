import SpecialValueInterface from "./SpecialValueInterface";
import Artisan from "../../../class/Artisan";

export default abstract class SpecialValue implements SpecialValueInterface {
    protected selected: boolean = false;

    protected constructor(protected readonly value: string) {
    }

    public select(value: string, label: string, otherwise: () => void): void {
        if (value === this.value) {
            this.selected = true;
        } else {
            otherwise();
        }
    }

    public deselect(value: string, label: string, otherwise: () => void): void {
        if (value === this.value) {
            this.selected = false;
        } else {
            otherwise();
        }
    }

    public checkSelected(value: string, otherwise: () => boolean): boolean {
        if (value === this.value) {
            return this.selected;
        } else {
            return otherwise();
        }
    }

    public clear(): void {
        this.selected = false;
    }

    public isSelected(): boolean {
        return this.selected;
    }

    public abstract matches(artisan: Artisan): boolean;
}
