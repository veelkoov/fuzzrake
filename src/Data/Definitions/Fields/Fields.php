<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Traits\UtilityClass;

final class Fields
{
    use UtilityClass;

    private static ?FieldsList $all = null;
    private static ?FieldsList $persisted = null;
    private static ?FieldsList $public = null;
    private static ?FieldsList $inIuForm = null;
    private static ?FieldsList $iuFormAffected = null;
    private static ?FieldsList $inStats = null;
    private static ?FieldsList $urls = null;
    private static ?FieldsList $nonInspected = null;

    public static function all(): FieldsList
    {
        return self::$all ??= new FieldsList(Field::cases());
    }

    public static function persisted(): FieldsList
    {
        return self::$persisted ??= self::all()->filtered(fn (Field $field): bool => $field->isPersisted());
    }

    public static function public(): FieldsList
    {
        return self::$public ??= self::all()->filtered(fn (Field $field): bool => $field->public());
    }

    public static function inIuForm(): FieldsList
    {
        return self::$inIuForm ??= self::all()->filtered(fn (Field $field): bool => $field->isInIuForm());
    }

    public static function iuFormAffected(): FieldsList
    {
        return self::$iuFormAffected ??= self::inIuForm()->plus(FieldsData::IU_FORM_AFFECTED);
    }

    public static function inStats(): FieldsList
    {
        return self::$inStats ??= self::all()->filtered(fn (Field $field): bool => $field->inStats());
    }

    public static function urls(): FieldsList
    {
        return self::$urls ??= self::all()->filtered(fn (Field $field): bool => str_starts_with($field->name, 'URL_'));
    }

    public static function nonInspectedUrls(): FieldsList
    {
        return self::$nonInspected ??= self::urls()->filtered(fn (Field $field): bool => in_array($field, FieldsData::NON_INSPECTED_URLS, true));
    }
}
