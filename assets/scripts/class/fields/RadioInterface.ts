export default interface RadioInterface {
  val(): null | string;

  isVal(value: string): boolean;

  isAnySelected(): boolean;

  selectedIdx(): number;
}
