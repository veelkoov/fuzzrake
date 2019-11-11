'use strict';

import Filter from "./Filter";
import Artisan from "./Artisan";

export default class FilterSimpleValue extends Filter {
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

        if (typeof artisan[this.fieldName] === 'boolean') {
            return this.isSelected(artisan[this.fieldName] ? '1' : '0');
        } else {
            return this.isSelected(artisan[this.fieldName]);
        }
    }

    protected getStatusText(): string {
        if (this.noneSelected()) {
            return 'any';
        }

        const anyOrAll = this.selectedLabels.length > 1 ? 'any of: ' : '';

        return anyOrAll + this.selectedLabels.join(', ')
            .replace(this.UNKNOWN_VALUE, 'Unknown')
            .replace(/ \(.+?\)/g, ''); // TODO: Drop () earlier
    }
}
