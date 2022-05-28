import AbstractBaseFilter from './AbstractBaseFilter';
import AbstractSingleFieldUnFilter from './AbstractSingleFieldUnFilter';
import AbstractSingleFieldUnOtFilter from './AbstractSingleFieldUnOtFilter';
import AllSetUnOtFilter from './AllSetUnOtFilter';
import AnySetUnOtFilter from './AnySetUnOtFilter';
import Artisan from '../../class/Artisan';
import FilterInterface from './FilterInterface';
import OtherValue from './special/OtherValue';
import Specie from '../../species/Specie';
import Species from '../../species/Species';
import StatusWriter from '../StatusWriter';
import UnknownValueTwoFields from './special/UnknownValueTwoFields';

export default class SpeciesFilter extends AbstractBaseFilter<string> {
    private readonly inFilter: AbstractSingleFieldUnOtFilter<string>;
    private readonly outFilter: AbstractSingleFieldUnFilter<string>;
    private readonly unknown: UnknownValueTwoFields;
    private readonly other: OtherValue;
    private recalculationRequired = true;

    public constructor(private readonly fieldNameIn: string, private readonly fieldNameOut: string, private readonly species: Species) {
        super();
        this.inFilter = new AnySetUnOtFilter<string>(fieldNameIn);
        this.outFilter = new AllSetUnOtFilter<string>(fieldNameOut);
        this.unknown = new UnknownValueTwoFields(fieldNameIn, fieldNameOut);
        this.other = new OtherValue(fieldNameIn);
    }

    public getStorageName(): string {
        return this.fieldNameIn;
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'any of', this.selectedLabels, this.unknown.isSelected() ? 'unknown' : undefined, this.other.isSelected() ? 'Other' : undefined);
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
