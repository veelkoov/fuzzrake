<?php

declare(strict_types=1);

namespace App\ValueObject\Routing;

use App\Utils\Traits\UtilityClass;

final class RouteName
{
    use UtilityClass;

    final public const string API_CREATOR = 'api_creator';
    final public const string API_CREATORS = 'api_creators';
    final public const string CONTACT = 'contact';
    final public const string CREATOR_IDS = 'creator_ids';
    final public const string DONATE = 'donate';
    final public const string EVENTS = 'events';
    final public const string EVENTS_ATOM = 'events_atom';
    final public const string FEEDBACK_FORM = 'feedback_form';
    final public const string FEEDBACK_SENT = 'feedback_sent';
    final public const string HTMX_MAIN_CREATORS_IN_TABLE = 'htmx_main_creators_in_table';
    final public const string HTMX_MAIN_CREATOR_CARD = 'htmx_main_creator_card';
    final public const string HTMX_MAIN_UPDATES_DIALOG = 'htmx_main_updates_dialog';
    final public const string HTMX_MENU = 'htmx_menu';
    final public const string INFO = 'info';
    final public const string IU_FORM_START = 'iu_form_step_start';
    final public const string IU_FORM_DATA = 'iu_form_step_data';
    final public const string IU_FORM_CONFIRMATION = 'iu_form_step_confirmation';
    final public const string MAIN = 'main';
    final public const string MX_CREATOR_EDIT = 'mx_creator_edit';
    final public const string MX_CREATOR_NEW = 'mx_creator_new';
    final public const string MX_CREATORS_URLS = 'mx_creators_urls';
    final public const string MX_CREATOR_URLS_SELECTION = 'mx_creator_urls_selection';
    final public const string MX_CREATOR_URLS_REMOVAL = 'mx_creator_urls_removal';
    final public const string MX_EVENT_EDIT = 'mx_event_edit';
    final public const string MX_EVENT_NEW = 'mx_event_new';
    final public const string MX_SUBMISSION = 'mx_submission';
    final public const string MX_SUBMISSIONS = 'mx_submissions';
    final public const string MX_SUBMISSIONS_SOCIAL = 'mx_submissions_social';
    final public const string MX_QUERY = 'mx_query';
    final public const string NEW_CREATORS = 'new_creators';
    final public const string GUIDELINES = 'guidelines';
    final public const string SITEMAP = 'sitemap';
    final public const string SHOULD_KNOW = 'should_know';
    final public const string STATISTICS = 'statistics';
    final public const string TRACKING = 'tracking';
}
