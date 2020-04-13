import Artisan from "../../class/Artisan";

export default interface FilterInterface {
    matches(artisan: Artisan): boolean;
    isActive(): boolean;
    select(value: string): void;
    deselect(value: string): void;
    clear(): void;
}
