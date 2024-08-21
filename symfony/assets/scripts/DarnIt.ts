export default class DarnIt {
  // I don't want to come up with a good name not conflicting with `Error`, so...
  private static alreadyReported = new Set<string>();

  public static report(
    message: string,
    consoleOnlyDetails: unknown,
    alertOnlyOnce: boolean,
  ): void {
    let consoleMessage = `ERROR: ${message}`;

    if (consoleOnlyDetails) {
      consoleMessage += ` | DETAILS: ${consoleOnlyDetails}`;
    }

    console.error(consoleMessage);

    if (!alertOnlyOnce || !DarnIt.alreadyReported.has(message)) {
      alert(
        `Darn it! An error occurred. You may try refreshing the page/clearing the cache/using incognito mode/using different browser/using different network.\n\n${message}`,
      );

      DarnIt.alreadyReported.add(message);
    }
  }
}
