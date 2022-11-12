export default interface FilterInterface {
    isActive(): boolean;

    setSelected(isSelected: boolean, value: string, label: string): void;

    select(value: string, label: string): void;

    deselect(value: string, label: string): void;

    isSelected(value: string): boolean;

    clear(): void;

    getStatus(): string;

    getStorageName(): string;
}
