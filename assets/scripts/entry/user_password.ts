import { zxcvbn, zxcvbnOptions } from "@zxcvbn-ts/core";
import * as zxcvbnCommonPackage from "@zxcvbn-ts/language-common";
import * as zxcvbnEnPackage from "@zxcvbn-ts/language-en";
import { requireJQ } from "../jQueryUtils";

const options = {
  translations: zxcvbnEnPackage.translations,
  graphs: zxcvbnCommonPackage.adjacencyGraphs,
  dictionary: {
    ...zxcvbnCommonPackage.dictionary,
    ...zxcvbnEnPackage.dictionary,
    userInputs: ["fursuit", "getfursu.it", "getfursuit", "maker"],
  },
};

zxcvbnOptions.setOptions(options);
const MIN_PASSWORD_LEN = 8; // grep-password-length

const feedbackElement = requireJQ("#password-feedback");
const explanationElement = requireJQ("#password-explanation");

requireJQ('input[type=password][name$="[newPassword][first]"]').on(
  "keyup change",
  function () {
    const element = jQuery(this);
    const password = <string>element.val();

    let feedback: string;
    const explanation: string[] = [];

    if (password.length < MIN_PASSWORD_LEN) {
      feedback = "This password is too short.";

      explanation.push(
        `Please use a password at least ${MIN_PASSWORD_LEN} characters long.`,
      );
    } else {
      const zxcvbnResult = zxcvbn(password);

      let adjective: string;
      switch (zxcvbnResult.score) {
        case 4:
          adjective = "good";
          break;
        case 3:
          adjective = "fine";
          break;
        case 2:
          adjective = "mediocre";
          break;
        case 1:
          adjective = "poor";
          break;
        default:
          adjective = "bad";
          break;
      }

      feedback = `This looks like a ${adjective} password.`;

      if (zxcvbnResult.feedback.warning !== null) {
        explanation.push(zxcvbnResult.feedback.warning);
      }

      explanation.push(
        ...zxcvbnResult.feedback.suggestions.map((value) =>
          value.replace("Add more words that are less common.", ""),
        ),
      );
      explanation.push(
        zxcvbnResult.score >= 3
          ? "(This rating could be wrong)"
          : "You can choose to still use this password, but it is not recommended.",
      );
    }

    feedbackElement.text(feedback);
    explanationElement.text(explanation.join(" "));
  },
);

// TODO:
// https://www.ncsc.gov.uk/collection/top-tips-for-staying-secure-online/password-managers
// https://www.cyber.gc.ca/en/guidance/password-managers-security-itsap30025
// https://www.cyber.gov.au/protect-yourself/securing-your-accounts/password-managers
// https://www.staysafeonline.org/articles/password-managers
