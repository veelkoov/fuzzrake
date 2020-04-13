import Artisan from "../../class/Artisan";

export default interface FilterVisInterface {
    isActive(): boolean;
    restoreChoices(): void;
    getFilterId(): string;
    getDataTableFilterCallback(artisans: Artisan[]): (_: any, __: any, index: number) => boolean;
}
