require('../../styles/general.less');

function scrollPastMenuBarOnHash(): void {
    if (window.location.hash) {
        scrollBy(0, -70); // FIXME: 70!!!
    }
}

jQuery(window).on('hashchange', scrollPastMenuBarOnHash);
jQuery(scrollPastMenuBarOnHash);
