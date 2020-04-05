'use strict';

import Filter from "./Filter";
import Artisan from "./Artisan";

export default class FilterSetSingle extends Filter {
    constructor(fieldName: string, idPart: string, protected readonly isAnd: boolean) {
        super(fieldName, idPart);
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

        if (this.selectedValues.length > 2) {
            return this.getStatusTextAnyAllOfPart() + ' '
                + this.selectedValues.length + ' selected';
        } else if (this.selectedValues.length === 2) {
            return this.getStatusTextAnyAllOfPart() + ': '
                + this.getSelectedLabelsCommaSeparated();
        } else {
            return this.getSelectedLabelsCommaSeparated();
        }
    }

    protected getStatusTextAnyAllOfPart(): string {
        return this.isAnd ? 'all of' : 'any of';
    }
}
