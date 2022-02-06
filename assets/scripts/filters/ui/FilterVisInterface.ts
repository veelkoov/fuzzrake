import Artisan from "../../class/Artisan";
import FilterInterface from "../data/FilterInterface";

export default interface FilterVisInterface {
    isActive(): boolean;

    restoreChoices(): void;

    saveChoices(): void;

    getFilterId(): string;

    matches(artisan: Artisan): boolean;

    getFilter(): FilterInterface;
}
