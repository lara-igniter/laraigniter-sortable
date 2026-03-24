<?php

namespace Laraigniter\Sortable;

/**
 * Holds the resolved default sort state set by Sortable::sortable().
 *
 * Used by SortableLink to display the active sort indicator on first load
 * (when no ?sort=&order= GET parameters are present in the URL).
 */
class SortableState
{
    /**
     * The column currently sorted by default.
     *
     * @var string|null
     */
    protected static ?string $column = null;

    /**
     * The direction of the default sort (asc|desc).
     *
     * @var string|null
     */
    protected static ?string $direction = null;

    /**
     * Store the resolved default sort column and direction.
     *
     * @param string $column
     * @param string $direction
     * @return void
     */
    public static function set(string $column, string $direction): void
    {
        static::$column    = $column;
        static::$direction = strtolower($direction);
    }

    /**
     * Get the default sort column.
     *
     * @return string|null
     */
    public static function column(): ?string
    {
        return static::$column;
    }

    /**
     * Get the default sort direction.
     *
     * @return string|null
     */
    public static function direction(): ?string
    {
        return static::$direction;
    }

    /**
     * Determine if the given column is the active default sort column.
     * Only relevant when no ?sort= request parameter is present.
     *
     * @param string $column
     * @return bool
     */
    public static function isActive(string $column): bool
    {
        return static::$column !== null && static::$column === $column;
    }

    /**
     * Reset the state (useful in tests or per-request isolation).
     *
     * @return void
     */
    public static function reset(): void
    {
        static::$column    = null;
        static::$direction = null;
    }
}

