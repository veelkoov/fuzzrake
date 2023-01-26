import CheckBox from './CheckBox.vue';

export default class CheckBoxes {
    public constructor(
        private readonly checkboxes: CheckBox[],
    ) {
    }

    public all(): void {
        for (let checkbox of this.checkboxes) {
            checkbox.check();
        }
    }

    public none(): void {
        for (let checkbox of this.checkboxes) {
            checkbox.uncheck();
        }
    }

    public invert(): void {
        for (let checkbox of this.checkboxes) {
            checkbox.invert();
        }
    }
}