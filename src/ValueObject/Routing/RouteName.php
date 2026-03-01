<?php

declare(strict_types=1);

namespace App\ValueObject\Routing;

use App\Utils\Traits\UtilityClass;

final class RouteName
{
    use UtilityClass;

    public const string CONTACT = 'contact';
    public const string CREATOR_IDS = 'creator_ids';
    public const string DONATE = 'donate';
    public const string EVENTS = 'events';
    public const string EVENTS_ATOM = 'events_atom';
    public const string FEEDBACK_FORM = 'feedback_form';
    public const string FEEDBACK_SENT = 'feedback_sent';
    public const string GUIDELINES = 'guidelines';
    public const string INFO = 'info';
    public const string MAIN = 'main';
    public const string NEW_CREATORS = 'new_creators';
    public const string SHOULD_KNOW = 'should_know';
    public const string SITEMAP = 'sitemap';
    public const string STATISTICS = 'statistics';
    public const string TRACKING = 'tracking';

    public const string API_CREATOR = 'api_creator';
    public const string API_CREATORS = 'api_creators';

    public const string HTMX_MAIN_CREATORS_IN_TABLE = 'htmx_main_creators_in_table';
    public const string HTMX_MAIN_CREATOR_CARD = 'htmx_main_creator_card';
    public const string HTMX_MAIN_UPDATES_DIALOG = 'htmx_main_updates_dialog';
    public const string HTMX_MENU = 'htmx_menu';

    public const string USER_REGISTER = 'user_register';
    public const string USER_LOGIN = 'user_login';
    public const string USER_REQUEST_PASSWORD_RESET = 'user_request_password_reset';
    public const string USER_LOGOUT = 'user_logout';
    public const string USER_MAIN = 'user_main';
    public const string USER_RESEND_VERIFICATION_EMAIL = 'user_resend_verification_email';
    public const string USER_VERIFY_EMAIL = 'user_verify_email';

    public const string USER_IU_FORM_CONFIRMATION = 'iu_form_step_confirmation';
    public const string USER_IU_FORM_DATA = 'iu_form_step_data';
    public const string USER_IU_FORM_START = 'iu_form_step_start';

    public const string MX_CREATORS_URLS = 'mx_creators_urls';
    public const string MX_CREATOR_EDIT = 'mx_creator_edit';
    public const string MX_CREATOR_NEW = 'mx_creator_new';
    public const string MX_CREATOR_URLS_REMOVAL = 'mx_creator_urls_removal';
    public const string MX_CREATOR_URLS_SELECTION = 'mx_creator_urls_selection';
    public const string MX_EVENT_EDIT = 'mx_event_edit';
    public const string MX_EVENT_NEW = 'mx_event_new';
    public const string MX_QUERY = 'mx_query';
    public const string MX_SUBMISSION = 'mx_submission';
    public const string MX_SUBMISSIONS = 'mx_submissions';
    public const string MX_SUBMISSIONS_SOCIAL = 'mx_submissions_social';
}
