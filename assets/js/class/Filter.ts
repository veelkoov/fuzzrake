'use strict';

import * as $ from "jquery";
import Artisan from "./Artisan";
import ClickEvent = JQuery.ClickEvent;

export default abstract class Filter {
    protected readonly UNKNOWN_VALUE: string = '?';

    protected readonly $checkboxes: JQuery<HTMLElement>;
    protected selectedValues: string[] = [];
    protected selectedLabels: string[] = [];
    protected $statusDisplay: JQuery<HTMLElement>;
    private $clearButton: JQuery<HTMLElement>;

    protected constructor(protected readonly fieldName: string, private readonly idPart: string) {
        this.$statusDisplay = $(`${this.getCtrlSelector()} .status`);

        let _this = this;
        this.$checkboxes = $(`${this.getBodySelector()} input[type=checkbox]`);
        this.$checkboxes.on('change', () => { _this.updateSelection(); });

        this.$clearButton = $(`${this.getCtrlSelector()} button.filter-ctrl-remove`);
        this.$clearButton.on('click', (evt: ClickEvent) => {
            evt.stopImmediatePropagation();

            this.$checkboxes.prop('checked', false);
            this.updateSelection();
        });

        this.updateStatusDisplay();
    }

    public updateSelection(): void {
        let oldSelection = this.selectedValues;

        this.selectedValues = [];
        this.selectedLabels = [];

        this.$checkboxes.filter(':checked').each((_: number, checkbox: HTMLElement) => {
            this.selectedValues.push(checkbox.getAttribute('value'));
            this.selectedLabels.push(checkbox.getAttribute('data-label'));
        });

        if (oldSelection != this.selectedValues) {
            this.updateStatusDisplay();
            this.saveChoices();
        }
    }

    public getDataTableFilterCallback(artisans: Artisan[]): (_, __, index: number) => boolean {
        let _this: Filter = this;
        let _artisans: Artisan[] = artisans;

        return function (_, __, index: number): boolean {
            return _this.matches(_artisans[index]);
        };
    }

    public restoreChoices(): void {
        let values: string = localStorage[`filters/${this.fieldName}/choices`];

        if (values) {
            let valuesArr: string[] = values.split('\n');

            this.$checkboxes.filter(
                (index: number, element: HTMLElement) => valuesArr.includes(element.getAttribute('value'))
            ).prop('checked', true);

            this.updateSelection();
        }
    }

    public saveChoices(): void {
        try {
            localStorage[`filters/${this.fieldName}/choices`] = this.selectedValues.join('\n');
        } catch (e) {
            // Not allowed? - I don't care then
        }
    }

    public hasAnyChoice(): boolean {
        return this.selectedValues.length !== 0;
    }

    public getBodySelector(): string {
        return '#filter-body-' + this.idPart;
    }

    public getCtrlSelector(): string {
        return '#filter-ctrl-' + this.idPart;
    }

    public getFieldName(): string {
        return this.fieldName;
    }

    protected abstract matches(artisan: Artisan): boolean;

    protected abstract getStatusText(): string;

    protected noneSelected(): boolean {
        return this.selectedValues.length === 0;
    }

    protected isSelected(value: string): boolean {
        return this.selectedValues.indexOf(value) !== -1;
    }

    protected includeUnknown(): boolean {
        return this.selectedValues.indexOf(this.UNKNOWN_VALUE) !== -1;
    }

    protected isUnknown(artisan: Artisan): boolean {
        return artisan[this.fieldName] === null || artisan[this.fieldName].length === 0;
    }

    protected getSelectedLabelsCommaSeparated(): string {
        return this.selectedLabels.join(', ')
            .replace(this.UNKNOWN_VALUE, 'Unknown')
            .replace(/ \(.+?\)/g, ''); // TODO: Drop () earlier
    }

    private updateStatusDisplay(): void {
        this.$statusDisplay.text(this.getStatusText());
        this.$clearButton.toggle(this.hasAnyChoice());
    }
}
