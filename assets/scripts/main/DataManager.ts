import DataBridge from '../data/DataBridge';
import MessageBus from './MessageBus';

export type DataRow = string[]|string|number|boolean|null;

export default class DataManager {
    private data: DataRow[] = [];

    public constructor(
        private readonly messageBus: MessageBus,
    ) {
        messageBus.listenQueryUpdates((newQuery: string) => this.queryUpdate(newQuery));
    }

    private queryUpdate(newQuery: string): void {
        jQuery.ajax(DataBridge.getApiUrl(`artisans-array.json?${newQuery}`), {
            success: (newData: DataRow[], _: JQuery.Ajax.SuccessTextStatus, __: JQuery.jqXHR): void => {
                this.data = newData;

                this.messageBus.notifyDataChange(this.data);
            },
            error: (jqXHR: JQuery.jqXHR<any>, textStatus: string, errorThrown: string): void => {
                alert('ERROR'); // TODO
            },
        });
    }
}
