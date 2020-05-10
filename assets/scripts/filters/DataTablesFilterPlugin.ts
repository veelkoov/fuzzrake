import FilterVisInterface from "./ui/FilterVisInterface";
import Artisan from "../class/Artisan";

export default class DataTablesFilterPlugin {
    public constructor(private readonly artisans: Artisan[],
                       private readonly filters: FilterVisInterface[]) {
    }

    public getCallback(): (s: object, d: Array<any>, i: number, o: any, c: number) => boolean {
        // noinspection JSUnusedLocalSymbols
        return (settings: object, data: Array<any>, index: number, orgData: any, counter: number): boolean => {
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
