import { requireJQ } from "../jQueryUtils";
import error from "../ErrorMessage";

declare global {
  interface Window {
    formRecaptchaValidationCallback: (token: string) => void;
  }
}

export default class Captcha {
  public static setupOnForm(formSelector: string): void {
    const $form = requireJQ(formSelector);

    this.setupValidationCallback($form);

    $form.on("submit", function (event, captchaOK): void {
      if (!captchaOK) {
        event.preventDefault();
        grecaptcha.execute();
      }
    });
  }

  private static setupValidationCallback($form: JQuery): void {
    const $_form = $form;

    window["formRecaptchaValidationCallback"] = function (token: string): void {
      try {
        requireJQ("#form_recaptcha_token").val(token);

        $_form.trigger("submit", [true]);
      } catch (exception) {
        error("Automatic captcha failed for unknown reason.")
          .withConsoleDetails(exception)
          .reportEachTime();
      }
    };
  }
}
