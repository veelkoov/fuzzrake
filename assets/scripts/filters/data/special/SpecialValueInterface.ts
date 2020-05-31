import Artisan from "../../../class/Artisan";

export default interface SpecialValueInterface {
    clear(): void;

    select(value: string, label: string, otherwise: (() => void)): void;

    deselect(value: string, label: string, otherwise: (() => void)): void;

    checkSelected(value: string, otherwise: (() => boolean)): boolean;

    isSelected(): boolean;

    matches(artisan: Artisan): boolean;
}
