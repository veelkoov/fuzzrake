declare var gtag;

export default class GTag {
    private constructor() {
    }

    public static event(action: string, details: {}): void {
        gtag('event', action, details);
    }
}
