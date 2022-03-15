import Artisan from "../class/Artisan";
import FilterInterface from "./data/FilterInterface";
import AgeAndSfwConfig from "../class/AgeAndSfwConfig";

export default class DataTablesFilterPlugin {
    public constructor(
        private readonly artisans: Artisan[],
        private readonly filters: FilterInterface[],
        private readonly config: AgeAndSfwConfig,
    ) {
    }

    public getCallback(): (s: object, d: Array<any>, i: number, o: any, c: number) => boolean {
        // noinspection JSUnusedLocalSymbols
        return (settings: object, data: Array<any>, index: number, orgData: any, counter: number): boolean => {
            if (this.config.getMakerMode()) {
                return true;
            }

            let artisan = this.artisans[index];

            for (let filter of this.filters) {
                if (!filter.matches(artisan)) {
                    return false;
                }
            }

            return true;
        }
    }
}
