import ValueFilterVis from "./ValueFilterVis";

export default class ValueCategorizedFilterVis extends ValueFilterVis {
    public constructor(idPart: string, fieldName: string) {
        super(idPart, fieldName);

        this.initCheckBoxesMultiswitches();
    }

    private initCheckBoxesMultiswitches(): void {
        jQuery(`${this.bodySelector} a`).each((_, element) => {
            let $a = jQuery(element);
            let $checkboxes = $a.parents('fieldset').find('input:checkbox');
            let checkedValueFunction: any = ValueCategorizedFilterVis.getCheckedValueFunction($a.data('action'));

            $a.on('click', function (event, __) {
                event.preventDefault();

                $checkboxes.prop('checked', checkedValueFunction);
                // filter.updateSelection(); // FIXME
            });
        });
    }

    private static getCheckedValueFunction(action: string): any {
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
}
