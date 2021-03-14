import Artisan from "../../class/Artisan";
import AnyOrOtherSetFilter from "./AnyOrOtherSetFilter";
import AllOrOtherSetFilter from "./AllOrOtherSetFilter";
import AbstractBaseFilter from "./AbstractBaseFilter";
import StatusWriter from "../StatusWriter";
import AbstractSingleFieldWithOthersFilter from "./AbstractSingleFieldWithOthersFilter";
import AbstractSingleFieldFilter from "./AbstractSingleFieldFilter";
import AbstractUnknownValue from "./special/AbstractUnknownValue";
import OtherValue from "./special/OtherValue";
import UnknownValueTwoFields from "./special/UnknownValueTwoFields";
import Specie from "../../species/Specie";
import Species from "../../species/Species";
import FilterInterface from "./FilterInterface";

export default class SpeciesFilter extends AbstractBaseFilter<string> {
    private inFilter: AbstractSingleFieldWithOthersFilter<string>;
    private outFilter: AbstractSingleFieldFilter<string>;
    private unknown: AbstractUnknownValue;
    private other: OtherValue;
    private recalculationRequired = true;

    public constructor(private readonly fieldNameIn: string, private readonly fieldNameOut: string, private readonly species: Species) {
        super();
        this.inFilter = new AnyOrOtherSetFilter<string>(fieldNameIn);
        this.outFilter = new AllOrOtherSetFilter<string>(fieldNameOut);
        this.unknown = new UnknownValueTwoFields(fieldNameIn, fieldNameOut);
        this.other = new OtherValue(fieldNameIn);
    }

    public getStorageName(): string {
        return this.fieldNameIn;
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), this.unknown.isSelected(), 'any of', this.selectedLabels, this.other.isSelected() ? 'Other' : undefined);
    }

    public matches(artisan: Artisan): boolean {
        if (this.recalculationRequired) {
            this.recalculateSet();
            this.recalculationRequired = false;
        }

        if (!this.isActive() || this.unknown.matches(artisan) || this.other.matches(artisan)) {
            return true;
        }

        return this.inFilter.matches(artisan) && !this.outFilter.matches(artisan);
    }

    public isActive(): boolean {
        return this.unknown.isSelected() || this.other.isSelected() || super.isActive();
    }

    public clear(): void {
        super.clear();
        this.other.clear();
        this.unknown.clear();

        this.recalculationRequired = true;
    }

    public select(value: string, label: string): void {
        this.unknown.select(value, label, () => {
            this.other.select(value, label, () => {
                super.select(value, label);
            });
        });

        this.recalculationRequired = true;
    }

    public deselect(value: string, label: string): void {
        this.unknown.deselect(value, label, () => {
            this.other.deselect(value, label, () => {
                super.deselect(value, label);
            });
        });

        this.recalculationRequired = true;
    }

    private recalculateSet(): void {
        this.inFilter.clear();
        this.outFilter.clear();

        for (let selected of this.selectedValues) {
            this.selectSpecieAndAncestors(this.species.flat[selected], this.inFilter);
            this.outFilter.select(selected, '');
        }
    }

    private selectSpecieAndAncestors(specie: Specie, filter: FilterInterface): void {
        filter.select(specie.name, '');

        for (let subspecie of specie.parents) {
            this.selectSpecieAndAncestors(subspecie, filter);
        }
    }
}
