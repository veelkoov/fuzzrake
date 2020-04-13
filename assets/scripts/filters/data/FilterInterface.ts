import Artisan from "../../class/Artisan";

export default interface FilterInterface {
    getDataTableFilterCallback(artisans: Artisan[]): (_: any, __: any, index: number) => boolean;
    matches(artisan: Artisan): boolean;
    isActive(): boolean;
    select(value: string): void;
    deselect(value: string): void;
    clear(): void;
}
