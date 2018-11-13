'use strict';

require('../css/general.less');

import $ from 'jquery';

$(document).ready(function () {
    $('a.art-link').click(function (evt) {
        window.open(this.href);
        evt.preventDefault();
    });
});
