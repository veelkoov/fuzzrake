import Artisan from "../../class/Artisan";

export default interface FilterVisInterface {
    isActive(): boolean;
    restoreChoices(): void;
    saveChoices(): void;
    getFilterId(): string;
    matches(artisan: Artisan): boolean;
}
