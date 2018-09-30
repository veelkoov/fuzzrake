'use strict';

$(document).ready(function () {
    $('a.art-link').click(function (evt) {
        window.open(this.href);
        evt.preventDefault();
    });
});
