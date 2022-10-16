import Captcha from '../class/Captcha';
import Radio from "../class/fields/Radio";
import {toggle} from "../jQueryUtils";

jQuery(() => {
    Captcha.setupOnForm('form[name="feedback"]');

    react_to_subject_changes();
});

function react_to_subject_changes(): void {
    const subject = new Radio('feedback[subject]', refresh);
    const $feedbackSubjectNotice = $('#feedback-subject-notice');
    const $feedbackSubmitOption = $('#feedback-submit-option');

    function refresh(immediate: boolean = false): void {
        let message: string = '';

        switch (subject.val()) {
            case 'Help me get a fursuit':
                message = 'getfursu.it maintainer does not assist individuals looking for a fursuit maker/studio or trying to contact one. You will not receive any kind of support except for having this website available for you, as-is.';
                break;

            case "Maker's commissions info (open/closed) is inaccurate":
                message = "Maker's commissions info is determined automatically based on contents of their websites/social media. <strong>This cannot be adjusted manually.</strong> Possible causes: A) maker didn't update the website/social account <strong>which is actually being analysed by getfursu.it</strong>, or B) false-positive (software error/limitations). Case A should be taken care of by the maker themselves, by updating the website/social account, or submitting a new address to track. You can report case B as \"Other\".";
                break;

            case "Other maker's information is (partially) outdated":
                message = "getfursu.it maintainer does not update information about makers. All the information needs to be updated by the makers themselves. If you know some information here is outdated, please remind the maker to send updates.";
                break;
        }

        $feedbackSubjectNotice.html(message);
        toggle($feedbackSubjectNotice, '' !== message, immediate ? 0 : 'fast');
        toggle($feedbackSubmitOption, '' === message, immediate ? 0 : 'fast');
    }

    refresh(true);
}
