import '../../3rd-party/flag-icon-css/css/flag-icon.css';
import '../../styles/main.scss';
import Checklist from '../components/Checklist';
import {toggle} from '../jQueryUtils';

// @ts-expect-error It is being created right here
window.htmx = require('htmx.org');

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

        const $inputs = $link.parents('fieldset').find('input'); // FIXME: Special items

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        $inputs.each((_, element) => {
            element.checked = changeFunction(element.checked);
        });
    });
})();
