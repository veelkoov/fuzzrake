'use strict';

import Artisan from "./Artisan";
import FilterSetSingle from "./FilterSetSingle";

export default class FilterSetWithOthers extends FilterSetSingle {
    protected readonly OTHER_VALUE: string = '*';

    protected readonly otherFieldName: string;

    constructor(fieldName: string, idPart: string, protected readonly isAnd: boolean) {
        super(fieldName, idPart, isAnd);

        this.otherFieldName = FilterSetWithOthers.getOtherFieldName(fieldName);
    }

    protected matches(artisan: Artisan): boolean {
        if (this.includeOther() && this.hasOther(artisan)) {
            return true;
        }

        return super.matches(artisan);
    }

    protected countTrueSelectedValues() {
        let result = super.countTrueSelectedValues();

        if (this.includeOther()) {
            result--;
        }

        return result;
    }

    protected getStatusText(): string {
        return super.getStatusText().replace(this.OTHER_VALUE, 'Other');
    }

    protected isUnknown(artisan: Artisan): boolean {
        return super.isUnknown(artisan) && !this.hasOther(artisan);
    }

    private includeOther(): boolean {
        return this.selectedValues.indexOf(this.OTHER_VALUE) !== -1;
    }

    private hasOther(artisan: Artisan): boolean {
        return artisan[this.otherFieldName].length !== 0;
    }

    private static getOtherFieldName(fieldName: string) {
        return 'other' + fieldName.charAt(0).toUpperCase() + fieldName.substr(1);
    }
}
