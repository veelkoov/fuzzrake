import * as Utils from "./utils";

function updateRequestUpdateModalWithRowData(artisan): void {
    $('#artisanNameUR').html(artisan.name);

    Utils.updateUpdateRequestData('updateRequestSingle', artisan);
}

function initRequestUpdateModal(): void {
    $('#updateRequestModal').on('show.bs.modal', function (event) {
        updateRequestUpdateModalWithRowData($(event.relatedTarget).closest('tr').data('artisan'));
    });
}

export function init(): (() => void)[] {
    return [
        () => {
            initRequestUpdateModal();
        },
    ];
}
