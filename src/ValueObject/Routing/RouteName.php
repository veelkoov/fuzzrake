<?php

declare(strict_types=1);

namespace App\ValueObject\Routing;

use App\Utils\Traits\UtilityClass;

final class RouteName
{
    use UtilityClass;

    public const API = 'api';
    public const API_ARTISANS = 'api_artisans';
    public const API_OLD_TO_NEW_MAKER_IDS_MAP = 'api_old_to_new_maker_ids_map';
    public const DATA_UPDATES = 'data_updates';
    public const DONATE = 'donate';
    public const EVENTS = 'events';
    public const HEALTH = 'health';
    public const INFO = 'info';
    public const IU_FORM = 'iu_form';
    public const IU_FORM_CONFIRMATION = 'iu_form_confirmation';
    public const MAIN = 'main';
    public const MAKER_IDS = 'maker_ids';
    public const MX_ARTISAN_EDIT = 'mx_artisan_edit';
    public const MX_ARTISAN_NEW = 'mx_artisan_new';
    public const MX_ARTISAN_URLS = 'mx_artisan_urls';
    public const MX_EVENT_EDIT = 'mx_event_edit';
    public const MX_EVENT_NEW = 'mx_event_new';
    public const MX_QUERY = 'mx_query';
    public const RULES = 'rules';
    public const SITEMAP = 'sitemap';
    public const STATISTICS = 'statistics';
    public const TRACKING = 'tracking';
}
