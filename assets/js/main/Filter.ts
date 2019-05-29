'use strict';

import * as $ from "jquery";
import Artisan from "./Artisan";

export default abstract class Filter {
    protected readonly UNKNOWN_VALUE: string = '?';

    protected readonly $checkboxes: JQuery<HTMLElement>;
    protected selectedValues: string[] = [];
    protected selectedLabels: string[] = [];
    protected $statusDisplay: JQuery<HTMLElement>;

    protected constructor(protected readonly fieldName: string,
                          public readonly containerSelector: string,
                          protected readonly refreshCallback: () => void) {

        this.$statusDisplay = $(`${containerSelector} .status`);
        this.updateStatusDisplay();

        let _this = this;
        this.$checkboxes = $(`${containerSelector} input[type=checkbox]`);
        this.$checkboxes.on('change', () => { _this.updateSelection(); });
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
            this.refreshCallback();
        }
    }

    public getDataTableFilterCallback(artisans: Artisan[]): (_, __, index: number) => boolean {
        let _this: Filter = this;
        let _artisans: Artisan[] = artisans;

        return function (_, __, index: number): boolean {
            return _this.matches(_artisans[index]);
        };
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

    private updateStatusDisplay(): void {
        this.$statusDisplay.text(this.getStatusText());
    }
}
