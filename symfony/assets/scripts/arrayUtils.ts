export function unique<T>(input: Array<T> | JQuery<T>): Array<T> {
  return [...new Set([...input])];
}
