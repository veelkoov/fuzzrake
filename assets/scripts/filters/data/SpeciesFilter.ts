import Artisan from "../../class/Artisan";
import AnyOrOtherSetFilter from "./AnyOrOtherSetFilter";
import AllOrOtherSetFilter from "./AllOrOtherSetFilter";
import FilterInterface from "./FilterInterface";

export default class SpeciesFilter<T> extends AnyOrOtherSetFilter<T> {
    private outFilter: FilterInterface;

    public constructor(fieldNameIn: string, private readonly fieldNameOut: string) {
        super(fieldNameIn);
        this.outFilter = new AllOrOtherSetFilter<T>(fieldNameOut);
    }

    public matches(artisan: Artisan): boolean {
        return super.matches(artisan); // && (!this.outFilter.isActive() || !this.outFilter.matches(artisan)); // FIXME
    }

    public clear(): void {
        super.clear();
        this.outFilter.clear();
    }

    public setSelected(isSelected: boolean, value: string, label: string): void {
        super.setSelected(isSelected, value, label);
        this.outFilter.setSelected(isSelected, value, label);
    }

    public select(value: string, label: string): void {
        super.select(value, label);
        this.outFilter.select(value, label);
    }

    public deselect(value: string, label: string): void {
        super.deselect(value, label);
        this.outFilter.deselect(value, label);
    }
}
