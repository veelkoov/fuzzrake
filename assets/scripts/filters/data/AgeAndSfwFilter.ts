import FilterInterface from "./FilterInterface";
import Artisan from "../../class/Artisan";
import AgeAndSfwConfig from "../../class/AgeAndSfwConfig";

export default class AgeAndSfwFilter implements FilterInterface {
    constructor(
        private config: AgeAndSfwConfig,
    ) {
    }

    clear(): void {
    }

    deselect(value: string, label: string): void {
    }

    getStatus(): string {
        return "";
    }

    getStorageName(): string {
        return "";
    }

    isActive(): boolean {
        return false;
    }

    isSelected(value: string): boolean {
        return false;
    }

    matches(artisan: Artisan): boolean {
        if (!this.config.getIsAdult() && true !== artisan.safeWorksWithMinors) {
            return false;
        }

        return !this.config.getWantsSfw() || (false === artisan.nsfwWebsite && false === artisan.nsfwSocial);
    }

    select(value: string, label: string): void {
    }

    setSelected(isSelected: boolean, value: string, label: string): void {
    }
}
