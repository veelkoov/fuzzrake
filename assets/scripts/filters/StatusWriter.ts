export default abstract class StatusWriter {
    private static readonly MAX_LABELS = 3;

    public static get(isActive: boolean, relation: string, selectedLabels: Set<string>, importantLabel?: string, additionalLabel?: string): string {
        if (!isActive) {
            return 'any';
        }

        let parts: string[] = [];

        if (importantLabel) {
            parts.push(importantLabel);
        }

        let labels = Array.from(selectedLabels);

        if (additionalLabel) {
            labels.unshift(additionalLabel);
        }

        if (selectedLabels.size > 0 || additionalLabel !== undefined) {
            parts.push(StatusWriter.formatLabels(labels, relation));
        }

        return parts.join(' or ');
    }

    protected static formatLabels(labels: string[], prefix_when_not_single: string): string {
        if (labels.length === 1) {
            return labels[0];
        }

        if (labels.length > this.MAX_LABELS) {
            return prefix_when_not_single + ' ' + labels.length + ' selected';
        } else {
            return prefix_when_not_single + ': ' + labels.join(', ');
        }
    }
}
