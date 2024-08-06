<?php

declare(strict_types=1);

namespace App\ValueObject\Routing;

use App\Utils\Traits\UtilityClass;

final class RouteName
{
    use UtilityClass;

    final public const API = 'api';
    final public const API_ARTISANS = 'api_artisans';
    final public const CONTACT = 'contact';
    final public const DONATE = 'donate';
    final public const EVENTS = 'events';
    final public const EVENTS_ATOM = 'events_atom';
    final public const FEEDBACK_FORM = 'feedback_form';
    final public const FEEDBACK_SENT = 'feedback_sent';
    final public const HTMX_CREATOR_CARD = 'htmx_main_creator_card';
    final public const HTMX_MAIN_PRIMARY_CONTENT = 'htmx_main_primary_content';
    final public const HTMX_UPDATES_DIALOG = 'htmx_main_updates_dialog';
    final public const INFO = 'info';
    final public const IU_FORM_START = 'iu_form_step_start';
    final public const IU_FORM_DATA = 'iu_form_step_data';
    final public const IU_FORM_CONTACT_AND_PASSWORD = 'iu_form_step_contact_and_password';
    final public const IU_FORM_CONFIRMATION = 'iu_form_step_confirmation';
    final public const MAIN = 'main';
    final public const MAKER_IDS = 'maker_ids';
    final public const MX_ARTISAN_EDIT = 'mx_artisan_edit';
    final public const MX_ARTISAN_NEW = 'mx_artisan_new';
    final public const MX_ARTISAN_URLS = 'mx_artisan_urls';
    final public const MX_EVENT_EDIT = 'mx_event_edit';
    final public const MX_EVENT_NEW = 'mx_event_new';
    final public const MX_SUBMISSION = 'mx_submission';
    final public const MX_SUBMISSIONS = 'mx_submissions';
    final public const MX_SUBMISSIONS_SOCIAL = 'mx_submissions_social';
    final public const MX_QUERY = 'mx_query';
    final public const NEW_ARTISANS = 'new_artisans';
    final public const GUIDELINES = 'guidelines';
    final public const SITEMAP = 'sitemap';
    final public const SHOULD_KNOW = 'should_know';
    final public const STATISTICS = 'statistics';
    final public const TRACKING = 'tracking';
}
