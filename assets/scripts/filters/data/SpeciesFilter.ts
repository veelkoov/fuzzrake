import Artisan from "../../class/Artisan";
import AnyOrOtherSetFilter from "./AnyOrOtherSetFilter";
import AllOrOtherSetFilter from "./AllOrOtherSetFilter";
import Species from "../../class/Species";
import Specie from "../../class/Specie";
import AbstractBaseFilter from "./AbstractBaseFilter";
import StatusWriter from "../StatusWriter";
import AbstractSingleFieldWithOthersFilter from "./AbstractSingleFieldWithOthersFilter";

export default class SpeciesFilter extends AbstractBaseFilter<string> {
    private inFilter: AbstractSingleFieldWithOthersFilter<string>;
    private outFilter: AbstractSingleFieldWithOthersFilter<string>;

    public constructor(private readonly fieldNameIn: string, private readonly fieldNameOut: string, private readonly species: Species) {
        super();
        this.inFilter = new AnyOrOtherSetFilter<string>(fieldNameIn);
        this.outFilter = new AllOrOtherSetFilter<string>(fieldNameOut);
    }

    public getStorageName(): string {
        return this.fieldNameIn;
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), this.inFilter.isUnknownSelected(), 'any of', this.selectedLabels, this.inFilter.isOtherSelected() ? 'Other' : undefined);
    }

    public matches(artisan: Artisan): boolean {
        return this.inFilter.matches(artisan); // && (!this.outFilter.isActive() || !this.outFilter.matches(artisan)); // FIXME
    }

    public clear(): void {
        super.clear();
        this.recalculateSet();
    }

    public setSelected(isSelected: boolean, value: string, label: string): void {
        super.setSelected(isSelected, value, label);
        this.recalculateSet();
    }

    public select(value: string, label: string): void {
        super.select(value, label);
        this.recalculateSet();
    }

    public deselect(value: string, label: string): void {
        super.deselect(value, label);
        this.recalculateSet();
    }

    private recalculateSet(): void {
        let calculated: Set<string> = new Set<string>();

        for (let selected of this.selectedValues) {
            this.addSpecieAndSubspeciesNames(this.species.flat[selected], calculated);
        }

        this.selectInInternalFilters(calculated);
    }

    private addSpecieAndSubspeciesNames(specie: Specie, calculated: Set<string>): void {
        calculated.add(specie.name);

        for (let subspecie of specie.children) {
            this.addSpecieAndSubspeciesNames(subspecie, calculated);
        }
    }

    private selectInInternalFilters(calculated: Set<string>): void {
        this.inFilter.clear();
        this.outFilter.clear();

        for (let specie of calculated) {
            this.inFilter.select(specie, '');
            this.outFilter.select(specie, '');
        }
    }
}
