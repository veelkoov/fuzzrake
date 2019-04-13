'use strict';

import Filter from "./Filter";
import Artisan from "./Artisan";

export default class FilterString extends Filter {
    constructor(protected readonly fieldName: string,
                public readonly containerSelector: string,
                protected readonly refreshCallback: () => void) {
        super(fieldName, containerSelector, refreshCallback);
    }

    protected matches(artisan: Artisan): boolean {
        if (this.noneSelected()) {
            return true;
        }

        if (this.includeUnknown() && this.isUnknown(artisan)) {
            return true;
        }

        return this.isSelected(artisan[this.fieldName]);
    }

    protected getStatusText(): string {
        if (this.noneSelected()) {
            return 'any';
        }

        const anyOrAll = this.selectedValues.length > 1 ? 'any of: ' : '';

        return anyOrAll + this.selectedValues.join(', ')
            .replace(this.UNKNOWN_VALUE, 'Unknown')
            .replace(/ \(.+?\)/g, ''); // TODO: Drop () earlier
    }
}
