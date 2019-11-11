'use strict';

require('../../css/general.less');

function scrollPastMenuBarOnHash(): void {
    if (window.location.hash) {
        scrollBy(0, -70); // FIXME: 70!!!
    }
}

$(window).on('hashchange', scrollPastMenuBarOnHash);
$(scrollPastMenuBarOnHash);
