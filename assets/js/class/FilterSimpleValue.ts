'use strict';

import Filter from "./Filter";
import Artisan from "./Artisan";

export default class FilterSimpleValue extends Filter {
    constructor(fieldName: string, idPart: string) {
        super(fieldName, idPart);
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

        if (this.selectedValues.length > 2) {
            return 'any of ' + this.selectedValues.length + ' selected';
        } else if (this.selectedValues.length === 2) {
            return 'any of: ' + this.getSelectedLabelsCommaSeparated();
        } else {
            return this.getSelectedLabelsCommaSeparated();
        }
    }
}
