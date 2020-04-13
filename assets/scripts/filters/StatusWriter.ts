export default abstract class StatusWriter {
    public static get(isActive: boolean, isUnknownSelected, relation: string, selectedLabels: Set<string>, additionalLabel?: string): string {
        if (!isActive) {
            return 'any';
        }

        let parts: string[] = [];

        if (isUnknownSelected) {
            parts.push('Unknown');
        }

        if (selectedLabels.size > 0 || additionalLabel !== undefined) {
            parts.push(StatusWriter.getLabels(selectedLabels, relation, 3, additionalLabel));
        }

        return parts.join(' or ');
    }

    protected static getLabels(selectedLabels: Set<string>, prefix: string, max: number, additionalLabel?: string): string {
        let selected = Array.from(selectedLabels);

        if (additionalLabel !== undefined) {
            selected.unshift(additionalLabel);
        }

        if (selected.length === 1) {
            return selected[0];
        }

        if (selected.length > max) {
            return prefix + ' ' + selected.length + ' selected';
        } else {
            return prefix + ': ' + selected.join(', ');
        }
    }
}
