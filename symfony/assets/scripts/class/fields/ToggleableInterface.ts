export default interface ToggleableInterface {
  toggle(available: boolean): void;
  isAvailable(): boolean;
}
