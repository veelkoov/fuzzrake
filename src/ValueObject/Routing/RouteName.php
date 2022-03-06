<?php

declare(strict_types=1);

namespace App\ValueObject\Routing;

use App\Utils\Traits\UtilityClass;

final class RouteName
{
    use UtilityClass;

    final public const API = 'api';
    final public const API_ARTISANS = 'api_artisans';
    final public const API_OLD_TO_NEW_MAKER_IDS_MAP = 'api_old_to_new_maker_ids_map';
    final public const DATA_UPDATES = 'data_updates';
    final public const DONATE = 'donate';
    final public const EVENTS = 'events';
    final public const EVENTS_ATOM = 'events_atom';
    final public const HEALTH = 'health';
    final public const INFO = 'info';
    final public const IU_FORM_START = 'route_iu_form_step_start';
    final public const IU_FORM_DATA = 'route_iu_form_step_data';
    final public const IU_FORM_CONTACT_AND_PASSWORD = 'route_iu_form_step_contact_and_password';
    final public const IU_FORM_CONFIRMATION = 'iu_form_confirmation';
    final public const MAIN = 'main';
    final public const MAKER_IDS = 'maker_ids';
    final public const MX_ARTISAN_EDIT = 'mx_artisan_edit';
    final public const MX_ARTISAN_NEW = 'mx_artisan_new';
    final public const MX_ARTISAN_URLS = 'mx_artisan_urls';
    final public const MX_EVENT_EDIT = 'mx_event_edit';
    final public const MX_EVENT_NEW = 'mx_event_new';
    final public const MX_QUERY = 'mx_query';
    final public const NEW_ARTISANS = 'new_artisans';
    final public const RULES = 'rules';
    final public const SITEMAP = 'sitemap';
    final public const STATISTICS = 'statistics';
    final public const TRACKING = 'tracking';
}
