import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import Checklist from '../main/Checklist';
import {toggle} from '../jQueryUtils';
import FiltersManager from '../main/FiltersManager';
import ColumnsManager from '../main/ColumnsManager';

import 'htmx.org';

// @ts-expect-error I am incompetent and I don't care to learn frontend
global.jQuery = require('jquery');

(function setUpChecklist(): void {
    new Checklist();
})();

(function setUpSpeciesFilter(): void {
    // Enable expanding subspecies using the ▶ button
    jQuery('#filtersModal .specie .toggle').on('click', function (): void {
        jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
    });

    // Set up the "any of the descendants is selected" indicators
    jQuery('#filtersModal .specie input').on('change', function (): void {
        const $allParentSpecieDivs = jQuery(this).parents('.subspecies').prevAll('.specie');

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        $allParentSpecieDivs.each((_, div) => {
            const $subspeciesInputs = jQuery(div).next('br').next('.subspecies').find('input');
            const $indicator = jQuery(div).find('.descendants-indicator');

            const anySubspecieSelected = $subspeciesInputs
                .filter((_, input) => input.checked).length > 0;

            $indicator.removeClass('d-none')
            toggle($indicator, anySubspecieSelected, 0);
        });
    });
})();

(function setUpAllNoneInvertLinks(): void {
    jQuery('#filtersModal .allNoneInvert').on('click', function (event): void {
        const $link = jQuery(event.target);

        let changeFunction: (prev: boolean) => boolean;

        if ($link.hasClass('all')) {
            changeFunction = (): boolean => true;
        } else if ($link.hasClass('none')) {
            changeFunction = (): boolean => false;
        } else {
            changeFunction = (prev: boolean): boolean => !prev;
        }

        const $inputs = $link.parents('fieldset').find('input:not(.special)');

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        $inputs.each((_, element) => {
            if (element instanceof HTMLInputElement) {
                element.checked = changeFunction(element.checked);
            }
        });
        $inputs.eq(0).trigger('change'); // I would expect the above actions would trigger it, but that is not the case.
    });
})();

(function setUpFiltersManager(): void {
    new FiltersManager();
})();

(function setUpColumnsManager(): void {
    new ColumnsManager('#creators-table', '#columns-visibility-links a');
})();
