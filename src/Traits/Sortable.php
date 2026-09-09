<?php

namespace Laraigniter\Sortable\Traits;

use Elegant\Database\Model\Model;
use Elegant\Foundation\Exceptions\MassAssignmentException;
use Elegant\Support\Arr;
use Elegant\Support\Collection;
use Laraigniter\Sortable\SortableState;

trait Sortable
{
    /**
     * @var array $orderBy
     */
    public array $orderBy = [];

    /**
     * Apply sorting to the current query builder state.
     *
     * @param array|string|null $defaultParameters
     * @return Model
     */
    public function sortable($defaultParameters = null): Model
    {
        if (request()->has('sort') && request()->has('order')) {
            $this->orderBy = $this->formatToParameters([
                request()->get('sort') => request()->get('order'),
            ]);
        } else {
            if (is_null($defaultParameters)) {
                $defaultParameters = $this->getDefaultSortable();
            }

            if (!is_null($defaultParameters)) {
                $this->orderBy = $this->formatToParameters($defaultParameters);
            }
        }

        foreach ($this->orderBy as $column => $direction) {
            // Dot-notation in $sortable (e.g. 'father.name') → resolve via JOIN.
            if (strpos($column, '.') !== false && in_array($column, $this->sortable ?? [])) {
                $this->applyRelationOrderBy($column, $direction);
                continue;
            }

            if (strpos($column, '.') === false) {
                $column = $this->table . '.' . $column;
            }

            $this->database->order_by($column, $direction);
        }

        // Inform SortableLink of the active sort so it renders the correct icon.
        if (!empty($this->orderBy)) {
            $col = array_key_first($this->orderBy);
            SortableState::set($col, $this->orderBy[$col]);
        }

        return $this;
    }

    /**
     * Apply a JOIN-based ORDER BY for a dot-notation sortable column.
     *
     * Resolves the hasOne relation, adds a LEFT JOIN aliased as
     * `{relation}_{foreign_table}`, selects `{relation}_{field}` as a scalar
     * attribute on each result row, and orders by the joined column.
     *
     * @param string $column
     * @param string $direction
     * @return void
     */
    private function applyRelationOrderBy(string $column, string $direction): void
    {
        [$relation, $field] = explode('.', $column, 2);

        $hasOne = $this->hasOne ?? [];

        if (!isset($hasOne[$relation])) {
            $this->database->order_by($this->table . '.' . str_replace('.', '_', $column), $direction);
            return;
        }

        $def = $hasOne[$relation];

        if (Arr::isAssoc($def)) {
            $foreignTable = $def['foreign_table'] ?? null;
            $foreignKey   = $def['foreign_key'] ?? 'id';
            $localKey     = $def['local_key'] ?? null;

            if (!$foreignTable || !$localKey) {
                return;
            }
        } else {
            // Short-form: ['ModelName', 'foreign_key', 'local_key']
            $foreignModelName = strtolower((string) $def[0]);
            $foreignKey       = $def[1];
            $localKey         = $def[2];

            $this->load->model($foreignModelName);
            $foreignTable = $this->{$foreignModelName}->table;
        }

        $alias = $relation . '_' . $foreignTable;

        $this->database->join(
            $foreignTable . ' AS ' . $alias,
            $alias . '.' . $foreignKey . ' = ' . $this->table . '.' . $localKey,
            'left'
        );

        // Nullify $this->columns so get_all() does not append a bare `*` after
        // our explicit SELECT — MySQL rejects `SELECT expr, *` syntax.
        // We emit table.* first instead, which is valid before other expressions.
        $this->columns = null;
        $this->database->select($this->table . '.*', false);
        $this->database->select($alias . '.' . $field . ' AS ' . $relation . '_' . $field, false);
        $this->database->order_by($alias . '.' . $field, $direction);
    }

    /**
     * Get sortable attributes from a model.
     *
     * @return array<int, string>
     */
    public function getSortables(): array
    {
        return $this->sortable;
    }

    /**
     * Static function to get sortable attributes.
     *
     * @return Collection
     */
    public static function sortables(): Collection
    {
        return new Collection((new self)->getSortables());
    }

    /**
     * Check search value from if exists in sortables and return value.
     *
     * @param string $search
     * @return string
     */
    public static function getSearchValue(string $search): string
    {
        return in_array($search, self::sortables()->toArray()) ? $search : '';
    }

    /**
     * Check search value if exists in sortables.
     *
     * @param string $search
     * @return bool
     */
    public static function hasSortable(string $search): bool
    {
        return in_array($search, self::sortables()->toArray());
    }

    /**
     * Returns the first element of defined sortable columns from the Model.
     *
     * @return string|null
     */
    private function getDefaultSortable(): ?string
    {
        $sortBy = config('sortable.default_first_column');

        if (is_null($sortBy)) {
            if (!isset($this->sortable)) {
                throw new MassAssignmentException(sprintf(
                    'Add [sortable] property to allow sorting on [%s] model.',
                    get_class($this)
                ));
            }

            $sortBy = Arr::first($this->sortable);
        }

        if (!is_null($sortBy)) {
            return $sortBy;
        }

        return null;
    }

    /**
     * @param array|string $array
     *
     * @return array
     */
    private function formatToParameters($array): array
    {
        if (empty($array)) {
            return [];
        }

        $defaultOrder = config('sortable.default_order') ?? 'asc';

        if (is_string($array)) {
            return [$array => $defaultOrder];
        }

        return (key($array) === 0) ? [$array[0] => $defaultOrder] : [
            key($array) => reset($array),
        ];
    }
}

