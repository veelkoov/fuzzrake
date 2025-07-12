<?php

declare(strict_types=1);

/**
 * `in_array` with fixed params order, and hardcoded strict.
 *
 * @param array<mixed> $haystack
 */
function arr_contains(array $haystack, mixed $needle): bool
{
    return in_array($needle, $haystack, true);
}

/**
 * `array_map` with fixed params order.
 *
 * @template K of array-key
 * @template InV
 * @template OutV
 *
 * @param array<K, InV>                              $array
 * @param (callable(InV): OutV)|(Closure(InV): OutV) $callback
 *
 * @return ($array is list<InV> ? list<OutV> : array<K, OutV>)
 */
function arr_map(array $array, callable|Closure $callback): array
{
    return array_map($callback, $array);
}

/**
 * `array_map` with fixed params order, returning a *L*ist.
 *
 * @template InV
 * @template OutV
 *
 * @param array<InV>                                 $array
 * @param (callable(InV): OutV)|(Closure(InV): OutV) $callback
 *
 * @return list<OutV>
 */
function arr_mapl(array $array, callable|Closure $callback): array
{
    return array_map($callback, array_values($array));
}

/**
 * `array_map` for *ITER*ables, with fixed params order, returning a *L*ist.
 *
 * @template InV
 * @template OutV
 *
 * @param iterable<InV>                              $iterable
 * @param (callable(InV): OutV)|(Closure(InV): OutV) $callback
 *
 * @return list<OutV>
 */
function iter_mapl(iterable $iterable, callable|Closure $callback): array
{
    return array_map($callback, array_values([...$iterable]));
}

/**
 * @template K of array-key
 * @template V of scalar|object
 *
 * @param array<K, ?V> $array
 *
 * @return array<K, V>
 */
function arr_filter_nulls(array $array): array
{
    return array_filter($array, static fn (mixed $value) => null !== $value);
}

/**
 * `array_filter` for *ITER*ables returning a *L*ist.
 *
 * @template V
 *
 * @param iterable<V>                            $iterable
 * @param (callable(V): bool)|(Closure(V): bool) $callback
 *
 * @return list<V>
 */
function iter_filterl(iterable $iterable, callable|Closure $callback): array
{
    return array_values(array_filter([...$iterable], $callback));
}

/**
 * `array_filter` returning a *L*ist.
 *
 * @template V
 *
 * @param array<V>                               $array
 * @param (callable(V): bool)|(Closure(V): bool) $callback
 *
 * @return list<V>
 */
function arr_filterl(array $array, callable|Closure $callback): array
{
    return array_values(array_filter($array, $callback));
}

/**
 * @template V
 *
 * @param array<V> $array
 *
 * @return list<V>
 */
function arr_sortl(array $array): array
{
    sort($array);

    return $array;
}

/**
 * @template V
 *
 * @param iterable<V> $iterable
 *
 * @return list<V>
 */
function iter_sortl(iterable $iterable): array
{
    return arr_sortl([...$iterable]);
}

/**
 * @template V
 *
 * @param array<V> $array
 *
 * @return V|null
 */
function array_first(array $array): mixed
{
    $key = array_key_first($array);

    if (null === $key) {
        return null;
    }

    return $array[$key];
}

/**
 * @template V
 *
 * @param array<V> $array
 *
 * @return V|null
 */
function array_last(array $array): mixed
{
    $key = array_key_last($array);

    if (null === $key) {
        return null;
    }

    return $array[$key];
}

function str_strip_prefix(string $subject, string $prefix): string
{
    return str_starts_with($subject, $prefix)
        ? substr($subject, strlen($prefix))
        : $subject;
}
