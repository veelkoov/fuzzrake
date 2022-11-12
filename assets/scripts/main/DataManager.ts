import DataBridge from '../data/DataBridge';
import TableManager from "./TableManager";

export default class DataManager {
    private data: (string|string[]|boolean|number)[] = []; // TODO: Typehint OK?

    public constructor(
        private tableManager: TableManager,
    ) {
    }

    public updateQuery(newQuery: string): void {
        jQuery.ajax(DataBridge.getApiUrl(`artisans-array.json?${newQuery}`), {
            success: (newData: any, _: JQuery.Ajax.SuccessTextStatus, __: JQuery.jqXHR): void => {
                this.data = newData;

                this.tableManager.updateWith(this.data);
            },
            error: (jqXHR: JQuery.jqXHR<any>, textStatus: string, errorThrown: string): void => {
                alert('ERROR'); // TODO
            },
        });
    }

    public get getData() {
        return this.data;
    }
}
