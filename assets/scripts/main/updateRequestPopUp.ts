import * as Utils from "./utils";
import Artisan from "../class/Artisan";

function updateRequestUpdateModalWithRowData(artisan: Artisan): void {
    jQuery('#artisanNameUR').html(artisan.name);

    Utils.updateUpdateRequestData('updateRequestSingle', artisan);
}

function initRequestUpdateModal(): void {
    jQuery('#updateRequestModal').on('show.bs.modal', function (event) {
        // @ts-ignore
        let relatedTarget = event.relatedTarget;

        if (relatedTarget instanceof HTMLElement) {
            updateRequestUpdateModalWithRowData(jQuery(relatedTarget)
                .closest('tr').data('artisan'));
        }
    });
}

export function init(): (() => void)[] {
    return [
        () => {
            initRequestUpdateModal();
        },
    ];
}
