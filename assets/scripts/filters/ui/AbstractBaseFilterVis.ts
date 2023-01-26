import FilterInterface from '../data/FilterInterface';
import FilterVisInterface from './FilterVisInterface';

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

        this.$statusDisplay = this.getStatusDisplay();
        this.$checkboxes = this.getCheckboxes();

        this.setupCheckboxes();

        this.refreshUi();
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

    private getCheckboxes(): JQuery<HTMLInputElement> {
        return jQuery(`${this.bodySelector} input[type=checkbox]`);
    }

    private dataFromUiToModel(): void {
        this.$checkboxes.each((index, element) => {
            this.filter.setSelected(element.checked, element.value, element.dataset.label);
        });
    }
}
