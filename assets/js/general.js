'use strict';

require('../css/general.less');

import $ from 'jquery';

$(function () {
    $('a.art-link').click(function (evt) {
        window.open(this.href);
        evt.preventDefault();
    });
});
