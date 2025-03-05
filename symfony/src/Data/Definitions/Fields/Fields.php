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
        return self::$all ??= FieldsList::fromValues(Field::cases(), static fn (Field $field) => $field->value)->freeze();
    }

    public static function persisted(): FieldsList
    {
        return self::$persisted ??= self::all()->filterValues(static fn (Field $field) => $field->isPersisted())->freeze();
    }

    public static function public(): FieldsList
    {
        return self::$public ??= self::all()->filterValues(static fn (Field $field) => $field->public())->freeze();
    }

    public static function inIuForm(): FieldsList
    {
        return self::$inIuForm ??= self::all()->filterValues(static fn (Field $field) => $field->isInIuForm())->freeze();
    }

    public static function readFromSubmissionData(): FieldsList
    {
        return self::inIuForm()->plus(Field::FORMER_MAKER_IDS->value, Field::FORMER_MAKER_IDS);
    }

    public static function iuFormAffected(): FieldsList
    {
        return self::$iuFormAffected ??= self::all()->filterValues(static fn (Field $field): bool => $field->isInIuForm() || $field->affectedByIuForm())->freeze();
    }

    public static function inStats(): FieldsList
    {
        return self::$inStats ??= self::all()->filterValues(static fn (Field $field): bool => $field->inStats())->freeze();
    }

    public static function urls(): FieldsList
    {
        return self::$urls ??= self::all()->filterValues(static fn (Field $field): bool => str_starts_with($field->value, 'URL_'))->freeze();
    }

    public static function nonInspectedUrls(): FieldsList
    {
        return self::$nonInspected ??= self::all()->filterValues(static fn (Field $field): bool => $field->notInspectedUrl())->freeze();
    }
}
