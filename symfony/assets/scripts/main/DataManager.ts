import AgeAndSfwConfig from '../class/AgeAndSfwConfig';
import DarnIt from '../DarnIt';
import MessageBus from './MessageBus';
import Static from '../Static';

export type DataRow = string[]|string|number|boolean|null;

export default class DataManager {
    private prevQuery: string|null = null;
    private readonly ageAndSfwConfig: AgeAndSfwConfig = AgeAndSfwConfig.getInstance();

    public constructor(
        private readonly messageBus: MessageBus,
    ) {
        messageBus.listenDataLoadRequests((newQuery: string, isExhaustive: boolean) => this.queryUpdate(newQuery, isExhaustive));
    }

    private queryUpdate(newQuery: string, isExhaustive: boolean): void {
        const usedQuery = isExhaustive ? `?${newQuery}` : this.getQueryWithMakerModeAndSfwOptions(newQuery);

        if (this.prevQuery === usedQuery) {
            return;
        }

        this.prevQuery = usedQuery;

        Static.showLoadingIndicator();

        jQuery.ajax(Static.getApiUrl(`artisans-array.json${usedQuery}`), {
            success: (newData: DataRow[]): void => {
                this.messageBus.notifyDataChange(newData);
            },
            error: this.displayError,
        });
    }

    private displayError(_: JQuery.jqXHR, textStatus: string|null, errorThrown: string|null): void {
        let details = '';

        if (errorThrown) {
            details = errorThrown;
        } else if (textStatus) {
            details = textStatus;
        }

        if ('' !== details) {
            details = ` The error was: ${details}`;
        }

        DarnIt.report(`The server returned unexpected response (or none).${details}`, '', false);
    }

    private getQueryWithMakerModeAndSfwOptions(newQuery: string): string {
        if (AgeAndSfwConfig.getInstance().getMakerMode()) {
            return '?isAdult=1&wantsSfw=0&wantsInactive=1';
        }

        let usedQuery = `?isAdult=${this.ageAndSfwConfig.getIsAdult() ? '1' : '0'}&wantsSfw=${this.ageAndSfwConfig.getWantsSfw() ? '1' : '0'}`;

        if ('' !== newQuery) {
            usedQuery += '&' + newQuery;
        }

        return usedQuery;
    }
}
