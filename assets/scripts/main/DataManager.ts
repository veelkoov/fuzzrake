import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import DataBridge from '../data/DataBridge';
import MessageBus from './MessageBus';

export type DataRow = string[]|string|number|boolean|null;

export default class DataManager {
    private data: DataRow[] = [];
    private readonly ageAndSfwConfig: AgeAndSfwConfig = AgeAndSfwConfig.getInstance();

    public constructor(
        private readonly messageBus: MessageBus,
    ) {
        messageBus.listenQueryUpdates((newQuery: string) => this.queryUpdate(newQuery));
    }

    private queryUpdate(newQuery: string): void {
        if (AgeAndSfwConfig.getInstance().getMakerMode()) {
            newQuery = '';
        }

        if ('' !== newQuery) { // TODO: Improve
            newQuery = `?${newQuery}&isAdult=${this.ageAndSfwConfig.getIsAdult() ? '1' : '0'}&wantsSfw=${this.ageAndSfwConfig.getWantsSfw() ? '1' : '0'}`
        }

        jQuery.ajax(DataBridge.getApiUrl(`artisans-array.json${newQuery}`), {
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
