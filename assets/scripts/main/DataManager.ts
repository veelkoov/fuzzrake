import DataBridge from '../data/DataBridge';

export default class DataManager {
    private data: (string|string[]|boolean|number)[] = []; // TODO: Typehint OK?

    public constructor(
        private dataUpdatedCallback: (DataManager) => void,
    ) {
    }


    public updateQuery(newQuery: string): void {
        jQuery.ajax(DataBridge.getApiUrl(`artisans-array.json?${newQuery}`), {
            success: (newData: any, _: JQuery.Ajax.SuccessTextStatus, __: JQuery.jqXHR): void => {
                this.data = newData;

                this.dataUpdatedCallback(this);
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
