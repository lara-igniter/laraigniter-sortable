<?php

namespace Laraigniter\Sortable\Traits;

use App\Core\MY_Model;
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
     * @return MY_Model
     */
    public function sortable($defaultParameters = null): MY_Model
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

        // Apply directly to the CI query builder so it is captured by
        // captureBuilderState() and survives the count_rows() reset in paginate().
        foreach ($this->orderBy as $column => $direction) {
            // Qualify unqualified columns with the model's own table so that any
            // JOIN which introduces a same-named column does not make the ORDER BY
            // clause ambiguous.
            if (strpos($column, '.') === false) {
                $column = $this->table . '.' . $column;
            }

            $this->database->order_by($column, $direction);
        }

        // Inform SortableLink of the active default sort so it can render
        // the correct icon/active state on first load (no GET params).
        if (!empty($this->orderBy)) {
            $col = array_key_first($this->orderBy);
            SortableState::set($col, $this->orderBy[$col]);
        }

        return $this;
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

