export default class Error {
    private static alreadyReported = new Set<string>();

    public static report(message: string, consoleOnlyDetails: string, alertOnlyOnce: boolean): void {
        let consoleMessage = `ERROR: ${message}`;

        if ('' !== consoleOnlyDetails) {
            consoleMessage += ` | DETAILS: ${consoleOnlyDetails}`;
        }

        console.log(consoleMessage);

        if (!alertOnlyOnce || !Error.alreadyReported.has(message)) {
            alert(`Darn it! An error occurred. You may try refreshing the page/clearing the cache/using incognito mode/using different browser/using different network.\n\n${message}`);

            Error.alreadyReported.add(message);
        }
    }
}
