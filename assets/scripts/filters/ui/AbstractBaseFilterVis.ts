import FilterVisInterface from "./FilterVisInterface";
import FilterInterface from "../data/FilterInterface";
import Artisan from "../../class/Artisan";
import ClickEvent = JQuery.ClickEvent;

export default abstract class AbstractBaseFilterVis implements FilterVisInterface {
    private readonly idPart: string;
    protected readonly $checkboxes: JQuery<HTMLInputElement>;
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
        this.setupAllNoneInvertLinks();

        this.refreshUi();
    }

    public matches(artisan: Artisan): boolean {
        return this.filter.matches(artisan);
    }

    public restoreChoices(): void {
        let stored: string = localStorage[`filters/${this.filter.getStorageName()}/choices`];

        if (stored) {
            let values: string[] = stored.split('\n');

            this.$checkboxes.each((index: number, element: HTMLInputElement): void => {
                element.checked = values.includes(element.value);
            });

            this.dataFromUiToModel();
            this.refreshUi();
        }
    }

    public saveChoices(): void {
        try {
            localStorage[`filters/${this.filter.getStorageName()}/choices`] = this.getSelectedChoices().join('\n');
        } catch (e) {
            // Not allowed? - I don't care then
        }
    }

    private getSelectedChoices(): Array<string> {
        let nonUnique = this.$checkboxes.filter(':checked')
            .map((_: number, element: HTMLInputElement): string => element.value).toArray();

        return [...new Set<string>(nonUnique)];
    }

    public getFilterId(): string {
        return this.idPart;
    }

    public isActive(): boolean {
        return this.filter.isActive();
    }

    public getFilter(): FilterInterface {
        return this.filter;
    }

    protected refreshUi(): void {
        this.$clearButton.toggle(this.filter.isActive());
        this.$statusDisplay.text(this.filter.getStatus());
    }

    private setupCheckboxes(): void {
        this.$checkboxes.on('change', (event: Event) => {
            if (event.currentTarget instanceof HTMLInputElement) {
                this.filter.setSelected(event.currentTarget.checked, event.currentTarget.value, event.currentTarget.dataset.label);

                this.refreshUi();
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

    private getCheckboxes(): JQuery<HTMLInputElement> {
        return jQuery(`${this.bodySelector} input[type=checkbox]`);
    }

    private setupClearButton(): void {
        this.$clearButton.on('click', (event: ClickEvent) => {
            event.stopImmediatePropagation();

            this.filter.clear();
            this.dataFromModelToUi();
            this.refreshUi();
        });
    }

    private setupAllNoneInvertLinks(): void {
        let _this = this;

        jQuery(`${this.bodySelector} .allNoneInvert a`).each((_, element) => {
            let $a = jQuery(element);
            let $checkboxes = $a.parents('fieldset').find('input:checkbox');
            let valueFunction: any = AbstractBaseFilterVis.getValueFunction($a.data('action'));

            $a.on('click', function (event, __) {
                event.preventDefault();

                $checkboxes.prop('checked', valueFunction);
                _this.dataFromUiToModel();
                _this.refreshUi();
            });
        });
    }

    private static getValueFunction(action: string): any {
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

    private dataFromModelToUi(): void {
        this.$checkboxes.each((index, element) => {
            element.checked = this.filter.isSelected(element.value);
        });
    }

    private dataFromUiToModel(): void {
        this.$checkboxes.each((index, element) => {
            this.filter.setSelected(element.checked, element.value, element.dataset.label);
        });
    }
}
