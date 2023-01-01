import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import MessageBus from './MessageBus';
import Static from '../Static';

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
        let usedQuery = this.getQueryWithMakerModeAndSfwOptions(newQuery);

        Static.showLoadingIndicator();

        jQuery.ajax(Static.getApiUrl(`artisans-array.json${usedQuery}`), {
            success: (newData: DataRow[], _: JQuery.Ajax.SuccessTextStatus, __: JQuery.jqXHR): void => {
                this.data = newData;

                this.messageBus.notifyDataChange(this.data);
            },
            error: (jqXHR: JQuery.jqXHR<any>, textStatus: string, errorThrown: string): void => {
                alert(`ERROR: ${textStatus}; ${errorThrown}`); // TODO
            },
        });
    }

    private getQueryWithMakerModeAndSfwOptions(newQuery: string): string {
        if (AgeAndSfwConfig.getInstance().getMakerMode()) {
            return '?isAdult=1&wantsSfw=0';
        }

        let usedQuery = `?isAdult=${this.ageAndSfwConfig.getIsAdult() ? '1' : '0'}&wantsSfw=${this.ageAndSfwConfig.getWantsSfw() ? '1' : '0'}`;

        if ('' !== newQuery) {
            usedQuery += '&' + newQuery;
        }

        return usedQuery;
    }
}
