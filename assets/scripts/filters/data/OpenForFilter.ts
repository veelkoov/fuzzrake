import AbstractSingleFieldFilter from "./AbstractSingleFieldFilter";
import Artisan from "../../class/Artisan";
import NotTrackedValue from "./special/NotTrackedValue";
import StatusWriter from "../StatusWriter";
import TrackingIssuesValue from "./special/TrackingIssuesValue";

export default class OpenForFilter<T> extends AbstractSingleFieldFilter<T> {
    private readonly trackingIssues: TrackingIssuesValue;
    private readonly notTracked: NotTrackedValue;

    public constructor(fieldName: string) {
        super(fieldName);
        this.trackingIssues = new TrackingIssuesValue();
        this.notTracked = new NotTrackedValue();
    }

    public clear(): void {
        super.clear();
        this.trackingIssues.clear();
        this.notTracked.clear();
    }

    public isActive(): boolean {
        return this.trackingIssues.isSelected() || this.notTracked.isSelected() || super.isActive();
    }

    public select(value: string, label: string): void {
        this.trackingIssues.select(value, label, () => {
            this.notTracked.select(value, label, () => {
                super.select(value, label);
            });
        });
    }

    public deselect(value: string, label: string): void {
        this.trackingIssues.deselect(value, label, () => {
            this.notTracked.deselect(value, label, () => {
                super.deselect(value, label);
            });
        });
    }

    public isSelected(value: string): boolean {
        return this.trackingIssues.checkSelected(value, () => {
            return this.notTracked.checkSelected(value, () => {
                return super.isSelected(value);
            });
        });
    }

    public matches(artisan: Artisan): boolean {
        if (!this.isActive() || this.notTracked.matches(artisan) || this.trackingIssues.matches(artisan)) {
            return true;
        }

        let target: Set<T> = artisan[this.fieldName];

        for (let value of this.selectedValues.values()) {
            if (target.has(value)) {
                return true;
            }
        }

        return false;
    }

    public getStatus(): string {
        return StatusWriter.get(this.isActive(), 'any of', this.selectedLabels, this.trackingIssues.isSelected() ? 'tracking issues' : undefined, this.notTracked.isSelected() ? 'not tracked' : undefined);
    }
}
