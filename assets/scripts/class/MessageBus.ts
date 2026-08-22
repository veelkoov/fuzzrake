export default class MessageBus {
  private static messages: Map<string, Array<() => void>> = new Map();

  public static listen(event: string, handler: () => void): void {
    if (!MessageBus.messages.has(event)) {
      MessageBus.messages.set(event, []);
    }

    MessageBus.messages.get(event)?.push(handler);
  }

  public static dispatch(event: string): void {
    console.info("Received event", event);

    MessageBus.messages.get(event)?.forEach((handler) => handler());
  }
}
