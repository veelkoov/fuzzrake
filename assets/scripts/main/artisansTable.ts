import {makerIdRegexp} from "../consts";
import DataBridge from "../class/DataBridge";
import Artisan from "../class/Artisan";
import {initFilters, applyFilters, restoreFilters, setRefreshCallback} from "./filters";
import Api = DataTables.Api;

const filtersButtonHtml = `<button id="filtersButton" type="button" class="btn btn-success" data-toggle="modal" data-target="#filtersModal">Choose filters</button>`;

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
        text: 'Choose columns'
    }],
    infoCallback: dataTableInfoCallback,
};

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

// noinspection OverlyComplexFunctionJS - DataTable's fault
function dataTableInfoCallback(settings: object, start: number, end: number, max: number, total: number, _: any) {
    return `<p class="small">Displaying ${total} out of ${max} fursuit makers in the database. &nbsp;
                <a href="${DataBridge.getDataUpdatesUrl()}"><span class="badge badge-warning">Studio missing?</span></a>
            </p>`;
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
            $jqDataTable.parents('.dataTables_wrapper').find('.dt-buttons').append(filtersButtonHtml);
            setRefreshCallback($dtDataTable.draw);
        },
        () => {
            initFilters();
        },
        () => {
            restoreFilters();
        },
        () => {
            applyFilters();
        },
    ];
}
