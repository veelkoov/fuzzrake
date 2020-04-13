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
        this.refreshStatusDisplay();
        this.setupAllNoneInvertLinks();
    }

    public matches(artisan: Artisan): boolean {
        return this.filter.matches(artisan);
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
                let value: string = event.currentTarget.value;
                let label: string = event.currentTarget.dataset.label;

                if (event.currentTarget.checked) {
                    this.filter.select(value, label);
                } else {
                    this.filter.deselect(value, label);
                }

                this.refreshClearButton();
                this.refreshStatusDisplay();
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
            this.refreshStatusDisplay();
        });
    }

    private refreshClearButton(): void {
        this.$clearButton.toggle(this.filter.isActive());
    }

    private setupAllNoneInvertLinks(): void {
        jQuery(`${this.bodySelector} a`).each((_, element) => {
            let $a = jQuery(element);
            let $checkboxes = $a.parents('fieldset').find('input:checkbox');
            let checkedValueFunction: any = AbstractBaseFilterVis.getCheckedValueFunction($a.data('action'));

            $a.on('click', function (event, __) {
                event.preventDefault();

                $checkboxes.prop('checked', checkedValueFunction);
                $checkboxes.trigger('change');
            });
        });
    }

    private static getCheckedValueFunction(action: string): any {
        switch (action) {
            case 'none':
                return false; // "function"
            case 'all':
                return true; // "function"
            case 'invert':
                return (_, checked) => !checked;
            default:
                throw new Error();
        }
    }

    private refreshStatusDisplay(): void {
        this.$statusDisplay.text(this.filter.getStatus());
    }
}
