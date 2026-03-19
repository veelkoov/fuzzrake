import "../../styles/admin.scss";

jQuery(() => {
  setupDirectiveButtons(
    "button.fix-button",
    (field, value) => `set ${field} "${value}"`,
  );
  setupDirectiveButtons("button.clear-button", (field) => `clear ${field}`);
  setupOpenAllNewLinksButton();
});

function setupDirectiveButtons(
  buttonsSelector: string,
  directiveToAdd: (field: string, value: string) => string,
) {
  jQuery(buttonsSelector).on("click", function (event) {
    const $valueRow = jQuery(event.target).parents("#mx-submission tr");
    const addedDirectives = directiveToAdd(
      $valueRow.data("field"),
      $valueRow.data("value"),
    );

    const directivesTextarea = jQuery("#submission_directives");
    const currentDirectives = (directivesTextarea.val() || "")
      .toString()
      .trim();
    directivesTextarea.val(
      `${currentDirectives}\n${addedDirectives}`.trim() + "\n",
    );
  });
}

function setupOpenAllNewLinksButton() {
  jQuery("#open-all-new-links").on("click", function (event) {
    event.preventDefault();

    jQuery('tr.after.changing[data-field^="URL_"]')
      .map((_, domElement) => (domElement.dataset["value"] || "").split(/\s+/))
      .each((index, url) => {
        window.open(url, `url_window_${index}`);
      });
  });
}
