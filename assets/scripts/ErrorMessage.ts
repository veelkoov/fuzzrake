export default function error(message: string): ErrorMessage {
  return new ErrorMessage(message);
}

export class ErrorMessage {
  private static alreadyReported = new Set<string>();

  private addGenericMessagePrefix: boolean = true;
  private detailsForConsole: unknown = null;

  constructor(private errorMessage: string) {}

  public skipGenericMessagePrefix(): ErrorMessage {
    this.addGenericMessagePrefix = false;

    return this;
  }

  public withConsoleDetails(details: unknown): ErrorMessage {
    this.detailsForConsole = details;

    return this;
  }

  public reportOnce(): void {
    this.logInConsole();

    const alertMessage = this.getAlertMessage();

    if (!ErrorMessage.alreadyReported.has(alertMessage)) {
      alert(alertMessage);

      ErrorMessage.alreadyReported.add(alertMessage);
    }
  }

  public reportEachTime(): void {
    this.logInConsole();

    alert(this.getAlertMessage());
  }

  private logInConsole(): void {
    let consoleMessage = `ERROR: ${this.errorMessage}`;

    if (null !== this.detailsForConsole) {
      consoleMessage += ` | DETAILS: ${this.detailsForConsole}`;
    }

    console.error(consoleMessage);
  }

  private getAlertMessage(): string {
    if (this.addGenericMessagePrefix) {
      return `Darn it! An error occurred. You may try refreshing the page/clearing the cache/using incognito mode/using different browser/using different network.\n\n${this.errorMessage}`;
    } else {
      return this.errorMessage;
    }
  }
}
