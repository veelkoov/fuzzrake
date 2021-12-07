function acknowledgedCallback(event: Event): void {
    event.preventDefault();

    jQuery('#scam-risk-warning, #scam-risk-acknowledged').toggle();

    // Anti-scam warning causes the user to be at the bottom of the table
    let offset = jQuery('#data-table-container').offset() || { 'top': 0 };
    window.scrollTo(0, offset.top - 5);
}

export function init(): (() => void)[] {
    return [
        () => {
            jQuery('#scam-risk-acknowledgement').on('click', acknowledgedCallback);
        },
    ];
}
