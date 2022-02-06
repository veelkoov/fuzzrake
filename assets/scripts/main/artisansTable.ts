import {makerIdRegexp} from "../consts";
import DataBridge from "../class/DataBridge";
import Artisan from "../class/Artisan";
import {setRefreshCallback} from "./filters";
import Api = DataTables.Api;

const additionalButtonsHtml = '\
        <button id="filtersButton" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#filtersModal"></button>\
        <button id="filtersButton" type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#legendModal">Legend</button>\
    ';

const dataTableOptions = {
    dom:
        "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col'ip>>",
    paging: false,
    autoWidth: false,
    columnDefs: [
        { targets: 'no-sorting', orderable: false },
        { targets: 'default-hidden', visible: false },
        { targets: 'maker-id', searchable: true },
        { targets: 'name', searchable: true },
        { targets: 'searchable', searchable: true },
        { targets: '_all', searchable: false },
    ],
    buttons: [{
        className: 'btn btn-dark',
        columns: '.toggleable',
        extend: 'colvis',
        text: 'Columns'
    }],
    infoCallback: dataTableInfoCallback,
};

const columnsSetVersion: string = '1';

/* $jqDataTable and $dtDataTable is the same object; i just don't know how to type hint it properly */
let $jqDataTable: JQuery<HTMLElement>;
let $dtDataTable: Api;
let $artisanRows: JQuery<HTMLElement>;

function highlightByMakerIdCallback(): void {
    $artisanRows.removeClass('matched-maker-id');

    let makerId = $dtDataTable.search().toUpperCase();

    if (makerId in DataBridge.getMakerIdsMap()) {
        makerId = DataBridge.getMakerIdsMap()[makerId];
    }

    if (makerId.match(makerIdRegexp)) {
        jQuery('#' + makerId.toUpperCase()).addClass('matched-maker-id');
    }
}

function recordColumnsVisibilityCallback(event: object, settings: object, column: object, state: boolean, _: boolean): void {
    try {
        localStorage['columns/version'] = columnsSetVersion
        // @ts-ignore ¯\_(ツ)_/¯
        let colVis: Array<boolean> = $dtDataTable.columns().visible();
        localStorage['columns/state'] = colVis.join(',');
    } catch (e) {
        // Not allowed? - I don't care then
    }
}

function restoreColumns(): void {
    let states: string = localStorage['columns/state'];

    if (localStorage['columns/version'] === columnsSetVersion && states) {
        let idx: number = 0;

        for (let state of states.split(',')) {
            $dtDataTable.columns(idx++).visible(state === 'true');
        }
    }
}

// noinspection OverlyComplexFunctionJS - DataTable's fault
function dataTableInfoCallback(settings: object, start: number, end: number, max: number, total: number, _: any) {
    return `<p class="small">Displaying ${total} out of ${max} fursuit makers in the database.</p>`;
}

export function init(): (() => void)[] {
    return [
        () => {
            $jqDataTable = jQuery('#artisans');
            $artisanRows = jQuery('#artisans tr.fursuit-maker');
        },
        () => {
            let artisans: Artisan[] = DataBridge.getArtisans();

            $artisanRows.each((index: number, item: HTMLElement) => {
                jQuery.data(item, 'artisan', artisans[index]);
            });
        },
        () => {
            $dtDataTable = $jqDataTable.DataTable(dataTableOptions);
            $jqDataTable.on('search.dt', highlightByMakerIdCallback);
            $jqDataTable.on('column-visibility.dt', recordColumnsVisibilityCallback);
            $jqDataTable.parents('.dataTables_wrapper').find('.dt-buttons').append(additionalButtonsHtml);
            setRefreshCallback($dtDataTable.draw);
        },
        () => {
            restoreColumns();
        },
    ];
}
