import CheckBox from './CheckBox.vue';

export default class CheckBoxes {
    public constructor(
        private readonly checkboxes: typeof CheckBox[],
    ) {
    }

    public all(): void {
        for (const checkbox of this.checkboxes) {
            checkbox.check();
        }
    }

    public none(): void {
        for (const checkbox of this.checkboxes) {
            checkbox.uncheck();
        }
    }

    public invert(): void {
        for (const checkbox of this.checkboxes) {
            checkbox.invert();
        }
    }
}
