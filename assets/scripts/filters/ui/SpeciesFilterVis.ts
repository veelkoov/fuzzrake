import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import SpeciesFilter from "../data/SpeciesFilter";
import Species from "../../class/Species";

export default class SpeciesFilterVis extends AbstractBaseFilterVis {
    private readonly markersByCheckboxId: { [id: string]: JQuery<HTMLSpanElement> };
    private readonly markers: JQuery<HTMLSpanElement>;

    public constructor(idPart: string, fieldNameIn: string, fieldNameOut: string, species: Species) {
        super(idPart, new SpeciesFilter(fieldNameIn, fieldNameOut, species));
        this.markersByCheckboxId = this.grabMarkersByCheckboxId();
        this.markers = this.grabMarkers(idPart);

        this.$checkboxes.on('change', (event) => {
            this.refreshCheckedAttributes(event.target);
            this.refreshDescentantsMarkers();
        });
    }

    private refreshCheckedAttributes(changed: HTMLInputElement): void {
        this.$checkboxes.filter('[value="' + changed.value + '"]').each((index, element) => {
            if (element.checked !== changed.checked) {
                element.checked = changed.checked;
            }
        });
    }

    private refreshDescentantsMarkers(): void {
        this.markers.hide();

        this.$checkboxes.filter(':checked').each((index, element) => {
            console.log(this.markersByCheckboxId[element.id]);
            this.markersByCheckboxId[element.id].show();
        });
    }

    private grabMarkersByCheckboxId(): { [id: string]: JQuery<HTMLSpanElement> } {
        let result = {};

        this.$checkboxes.each((index, element) => {
            result[element.id] = jQuery(element).siblings('label').find('span.descendants-indicator');
        });

        return result;
    }

    private grabMarkers(idPart: string): JQuery<HTMLSpanElement> {
        return jQuery(`${this.bodySelector} span.descendants-indicator`);
    }
}
