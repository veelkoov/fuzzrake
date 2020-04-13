import FilterVisInterface from "./FilterVisInterface";
import FilterInterface from "../data/FilterInterface";
import Artisan from "../../class/Artisan";
import ClickEvent = JQuery.ClickEvent;

export default abstract class AbstractBaseFilterVis implements FilterVisInterface {
    private readonly idPart: string;
    private readonly $checkboxes: JQuery<HTMLElement>;
    private readonly $clearButton: JQuery<HTMLElement>;
    private readonly $statusDisplay: JQuery<HTMLElement>;
    private readonly filter: FilterInterface;
    protected readonly ctrlSelector: string;
    protected readonly bodySelector: string;

    protected constructor(idPart: string, filter: FilterInterface) {
        this.idPart = idPart;
        this.filter = filter;
        this.ctrlSelector = this.getCtrlSelector();
        this.bodySelector = this.getBodySelector();

        this.$clearButton = this.getClearButton();
        this.$statusDisplay = this.getStatusDisplay();
        this.$checkboxes = this.getCheckboxes();

        this.setupCheckboxes();
        this.setupClearButton();
        this.refreshClearButton();
    }

    public getDataTableFilterCallback(artisans: Artisan[]): (_: any, __: any, index: number) => boolean {
        return this.filter.getDataTableFilterCallback(artisans);
    }

    public restoreChoices(): void {
        // TODO
    }

    public getFilterId(): string {
        return this.idPart;
    }

    public isActive(): boolean {
        return this.filter.isActive();
    }

    private setupCheckboxes(): void {
        this.$checkboxes.on('change', (event: Event) => {
            if (event.currentTarget instanceof HTMLInputElement) {
                if (event.currentTarget.checked) {
                    this.filter.select(event.currentTarget.value);
                } else {
                    this.filter.deselect(event.currentTarget.value);
                }

                this.refreshClearButton();
            }
        });
    }

    private getBodySelector(): string {
        return `#filter-body-${this.idPart}`;
    }

    private getCtrlSelector(): string {
        return `#filter-ctrl-${this.idPart}`;
    }

    private getStatusDisplay(): JQuery<HTMLElement> {
        return jQuery(`${this.ctrlSelector} .status`);
    }

    private getClearButton(): JQuery<HTMLElement> {
        return jQuery(`${this.ctrlSelector} button.filter-ctrl-remove`);
    }

    private getCheckboxes(): JQuery<HTMLElement> {
        return jQuery(`${this.bodySelector} input[type=checkbox]`);
    }

    private setupClearButton(): void {
        this.$clearButton.on('click', (event: ClickEvent) => {
            event.stopImmediatePropagation();

            this.$checkboxes.prop('checked', false);
            this.filter.clear();
            this.refreshClearButton();
        });
    }

    private refreshClearButton(): void {
        this.$clearButton.toggle(this.filter.isActive());
    }
}
