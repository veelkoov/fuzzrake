import '../../styles/admin.scss';

jQuery(function () {
    jQuery('button.fix-button').on('click', function (event) {
        const directivesTextarea = jQuery('#submission_directives');

        const $valueRow = jQuery(event.target).parents('#mx-submission tr');
        const field = $valueRow.data('field');
        const value = $valueRow.data('value');

        directivesTextarea.val(`${directivesTextarea.val()}set ${field} "${value}"\n`);
    });
});
