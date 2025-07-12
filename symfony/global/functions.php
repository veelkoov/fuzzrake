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
 * `array_map` for iterables with fixed params order.
 *
 * @template InV
 * @template OutV
 *
 * @param iterable<InV>                              $iterable
 * @param (callable(InV): OutV)|(Closure(InV): OutV) $callback
 *
 * @return list<OutV>
 */
function iter_map(iterable $iterable, callable|Closure $callback): array
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
 * `array_filter` for iterables returning a list.
 *
 * @template V
 *
 * @param iterable<V>                            $iterable
 * @param (callable(V): bool)|(Closure(V): bool) $callback
 *
 * @return list<V>
 */
function iter_filter(iterable $iterable, callable|Closure $callback): array
{
    return array_values(array_filter([...$iterable], $callback));
}

/**
 * `array_filter` for lists.
 *
 * @template V
 *
 * @param list<V>                                $array
 * @param (callable(V): bool)|(Closure(V): bool) $callback
 *
 * @return list<V>
 */
function list_filter(array $array, callable|Closure $callback): array
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
function list_sort(array $array): array
{
    sort($array);

    return $array;
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
