import '../../styles/admin.scss';

jQuery(function () {
    jQuery('button.fix-button').on('click', function (event) {
        const directivesTextarea = jQuery('#submission_directives');

        const $valueRow = jQuery(event.target).parents('#mx-submission tr');
        const field = $valueRow.data('field');
        const value = $valueRow.data('value');

        const currentDirectives = directivesTextarea.val().toString().trimEnd();

        directivesTextarea.val(`${currentDirectives}\nset ${field} "${value}"\n`);
    });

    jQuery('#open-all-new-links').on('click', function (event) {
        event.preventDefault();

       jQuery('tr.after.changing[data-field^="URL_"]')
           .map((_, domElement) => domElement.dataset['value'].split('\n'))
           .each((index, url) => { window.open(url, `url_window_${index}`); });
    });
});
