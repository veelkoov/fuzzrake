function acknowledgedCallback(event): void {
    event.preventDefault();

    $('#scam-risk-warning, #scam-risk-acknowledged').toggle();

    // Anti-scam warning causes the user to be at the bottom of the table
    window.scrollTo(0, $('#data-table-container').offset().top - 70); // FIXME: 70!!!
}

export function init(): (() => void)[] {
    return [
        () => {
            $('#scam-risk-acknowledgement').on('click', acknowledgedCallback);
        },
    ];
}
