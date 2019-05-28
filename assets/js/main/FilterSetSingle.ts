'use strict';

import Filter from "./Filter";
import Artisan from "./Artisan";

export default class FilterSetSingle extends Filter {
    constructor(protected readonly fieldName: string,
                public readonly containerSelector: string,
                protected readonly refreshCallback: () => void,
                protected readonly isAnd: boolean) {
        super(fieldName, containerSelector, refreshCallback);
    }

    protected matches(artisan: Artisan): boolean {
        if (this.noneSelected() || this.includeUnknown() && this.isUnknown(artisan)) {
            return true;
        }

        let selectedCount = this.countTrueSelectedValues();

        let foundCount = 0;

        artisan[this.fieldName].forEach((value, _, __) => {
            if (this.isSelected(value)) {
                foundCount++;
            }
        });

        return foundCount > 0 && (!this.isAnd || foundCount === selectedCount);
    }

    protected countTrueSelectedValues() {
        let result = this.selectedValues.length;

        if (this.includeUnknown()) {
            result--;
        }

        return result;
    }

    protected getStatusText(): string {
        if (this.noneSelected()) {
            return 'any';
        }

        return this.getAnyOrAllStatusTextPart()
            + this.selectedLabels.join(', ')
                .replace(this.UNKNOWN_VALUE, 'Unknown')
                .replace(/ \(.+?\)/g, ''); // TODO: Drop () earlier
    }

    protected getAnyOrAllStatusTextPart() {
        return this.selectedValues.length > 1 ? (this.isAnd ? 'all of: ' : 'any of: ') : '';
    }
}
