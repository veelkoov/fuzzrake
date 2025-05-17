export type FieldPartState = { value: string; checked: boolean | null };
export type FieldPartsStates = Array<FieldPartState>;
export type FieldsStates = { [key: string]: FieldPartsStates };
