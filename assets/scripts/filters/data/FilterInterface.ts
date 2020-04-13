import Artisan from "../../class/Artisan";

export default interface FilterInterface {
    matches(artisan: Artisan): boolean;
    isActive(): boolean;
    select(value: string, label: string): void;
    deselect(value: string, label: string): void;
    clear(): void;
    getStatus(): string;
}
