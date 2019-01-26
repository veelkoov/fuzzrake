import * as $ from "jquery";

export default class Filter {
    private readonly dataColumnIndex: number;
    private readonly selector: string;
    private readonly isAnd: boolean;
    private readonly refreshCallback: () => void;
    private readonly $checkboxes: JQuery;
    private selectedValues: string[];

    constructor(dataColumnIndex: number, selector: string, isAnd: boolean, refreshCallback: () => void) {
        this.dataColumnIndex = dataColumnIndex;
        this.selector = selector;
        this.isAnd = isAnd;
        this.refreshCallback = refreshCallback;

        this.$checkboxes = $(`${selector} input[type=checkbox]`);
        this.selectedValues = [];

        let _this = this;

        this.$checkboxes.on('change', function () {
            _this.updateSelection();
        });
    }

    public updateSelection(): void {
        let oldSelection = this.selectedValues;

        this.selectedValues = this.$checkboxes.filter(':checked')
            .map((_, checkbox) => checkbox.getAttribute('value'))
            .get();

        if (oldSelection != this.selectedValues) {
            this.refreshCallback();
        }
    }

    public getDataTableFilterCallback(): (_, data: object, __) => boolean {
        let _this: Filter = this;

        return function (_, data: object, __) {
            let selectedCount = _this.selectedValues.length;

            if (selectedCount === 0) {
                return true;
            }

            let showUnknown = _this.selectedValues.indexOf('') !== -1;


            if (showUnknown && data[_this.dataColumnIndex].trim() === '') {
                return true;
            }

            let selectedNoUnknownCount = showUnknown ? selectedCount - 1 : selectedCount;
            let count = 0;

            data[_this.dataColumnIndex].split(',').forEach(function (value, _, __) {
                if (_this.selectedValues.indexOf(value.trim()) !== -1) {
                    count++;
                }
            });

            return count > 0 && (!_this.isAnd || count === selectedNoUnknownCount);
        };
    }
}
