import Artisan from '../class/Artisan';
import DataBridge from '../data/DataBridge';
import TableManager from './TableManager';
import {artisanFromArray} from './utils';

export type DataRow = string[]|string|number|boolean|null;

export default class DataManager {
    private data: DataRow[] = [];

    public constructor(
        private tableManager: TableManager,
    ) {
    }

    public updateQuery(newQuery: string): void {
        jQuery.ajax(DataBridge.getApiUrl(`artisans-array.json?${newQuery}`), {
            success: (newData: DataRow[], _: JQuery.Ajax.SuccessTextStatus, __: JQuery.jqXHR): void => {
                this.data = newData;

                this.tableManager.updateWith(this.data);
            },
            error: (jqXHR: JQuery.jqXHR<any>, textStatus: string, errorThrown: string): void => {
                alert('ERROR'); // TODO
            },
        });
    }

    public getArtisanByIndex(index: number): Artisan {
        return artisanFromArray(this.data[index]);
    }
}
