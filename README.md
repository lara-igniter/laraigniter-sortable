# lara-igniter/laraigniter-sortable

Column-sortable behavior for the **Laraigniter** framework.  
Provides a model trait, a Blade directive, and a config file that work natively with CodeIgniter 3's query builder and the `lara-igniter/framework` glue layer.

---

## Package structure (repository root)

```
laraigniter-sortable/
├── composer.json
├── config/
│   └── sortable.php          ← package defaults (CI $config = [...] format)
└── src/
    ├── SortableServiceProvider.php
    ├── SortableLink.php       ← renders the <a> sort anchor + icon
    ├── SortableState.php      ← holds the active default sort per request
    └── Traits/
        └── Sortable.php       ← use in any MY_Model subclass
```

---

## Installation

### 1. Install with Composer

```bash
composer require lara-igniter/laraigniter-sortable
```

### 2. (Optional) Use a local checkout during development

If you are developing the package locally, add a `path` repository in your app's root `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../laraigniter-sortable",
        "options": { "symlink": true }
    }
],
"require": {
    "lara-igniter/laraigniter-sortable": "*"
}
```

```bash
composer update lara-igniter/laraigniter-sortable
```

### 3. Register the service provider (only if auto-discovery is disabled)

The package declares its provider in `composer.json` under `extra.laraigniter.providers`, so in standard setups no manual registration is needed.

If your project disables package discovery, register it in `config/hooks.php`:

```php
$hook = Hooks::autoload([
    'providers' => [
        // ...existing providers...

        /*
         * Package Service Providers...
         */
        Laraigniter\Sortable\SortableServiceProvider::class,
    ],
]);
```

That's it. The provider runs on the `post_controller_constructor` hook, so it is always ready before any controller method or Blade view executes.

---

## Configuration

### How config loading works

The `SortableServiceProvider` uses a `mergeConfigFrom()` method that **prepends** the package root to CodeIgniter's `_config_paths` array.

When `config('sortable.xxx')` is called for the first time:

1. CI's lazy loader finds `vendor/lara-igniter/laraigniter-sortable/config/sortable.php` (or your local path checkout) → loads it as the section `sortable`.
2. If an app-level `config/sortable.php` exists, it is found next and **merged on top** (`array_merge`) — **app values always win**.
3. If no app-level file exists, the package defaults are used as-is, and CI returns `TRUE` without an error.

This mirrors exactly how `loadViewsFrom()` works in `Elegant\Pagination\PaginationServiceProvider`.

### Publishing the config (to override defaults)

```bash
php artisan vendor:publish --tag=sortable
```

This copies `vendor/lara-igniter/laraigniter-sortable/config/sortable.php` → `config/sortable.php`.  
Edit the published file — your values will automatically override the package defaults.

### Available options (`config/sortable.php`)

| Key | Default | Description |
|-----|---------|-------------|
| `columns` | `alpha / amount / numeric` groups | Maps column names to icon class + sort type |
| `enable_icons` | `true` | Show/hide the sort icon next to the link text |
| `default_icon_set` | `fa fa-sort` | Icon class used for unrecognised column types |
| `default_icon_type` | `default` | Fallback type key for suffix lookup |
| `sortable_icon` | `fa fa-sort` | Icon shown when the column is not currently sorted |
| `clickable_icon` | `false` | When `true` the icon itself is wrapped inside the `<a>` |
| `icon_text_separator` | `''` (empty string) | String/HTML placed between the link text and the icon |
| `asc_default_suffix` | `-asc` | Icon suffix for ascending on untyped columns |
| `desc_default_suffix` | `-desc` | Icon suffix for descending on untyped columns |
| `asc_alpha_suffix` | `-asc` | Icon suffix for ascending on alpha columns |
| `desc_alpha_suffix` | `-desc` | Icon suffix for descending on alpha columns |
| `asc_amount_suffix` | `-asc` | Icon suffix for ascending on amount columns |
| `desc_amount_suffix` | `-desc` | Icon suffix for descending on amount columns |
| `asc_numeric_suffix` | `-asc` | Icon suffix for ascending on numeric columns |
| `desc_numeric_suffix` | `-desc` | Icon suffix for descending on numeric columns |
| `anchor_class` | `null` | CSS class on every sort `<a>` — none added when `null` |
| `active_anchor_class` | `null` | Extra class added when the column is the active sort |
| `order_anchor_class_prefix` | `null` | Prefix for a dynamic direction class on the active anchor |
| `formatting_function` | `null` | Callable applied to the column title (e.g. `mb_strtoupper`) |
| `title_inside_anchor` | `false` | Use the raw column name as title when none is passed |
| `format_custom_titles` | `true` | Apply `formatting_function` to explicit titles too |
| `inject_title_as` | `null` | Query-string key to inject the formatted title into |
| `default_order` | `asc` | Direction used when no explicit order is provided |
| `default_order_unsorted` | `asc` | Direction used for columns not currently sorted |
| `default_first_column` | `id` | Column used when the model has no `?sort=` param and no explicit default |

---

## Usage

### 1. Add the trait to your model

```php
<?php

namespace App\Models;

use App\Core\MY_Model;
use Laraigniter\Sortable\Traits\Sortable;

class User extends MY_Model
{
    use Sortable;

    /**
     * The sortable attributes.
     *
     * @var array<int, string>
     */
    public array $sortable = [
        'id',
        'name',
        'created_at',
    ];
}
```

### 2. Call `->sortable()` in your repository / controller

```php
// Sorts by ?sort= & ?order= from the request.
// Falls back to config('sortable.default_first_column') when no params are present.
$users = (new User())->sortable()->paginate(15);

// Or pass an explicit default:
$users = (new User())->sortable('name')->paginate(15);

// Or pass a default direction too:
$users = (new User())->sortable(['created_at' => 'desc'])->paginate(15);
```

`sortable()` qualifies the column with the model's own table name automatically, so joins never produce ambiguous `ORDER BY` clauses.

### 3. Use `@sortable` in your Blade view

The `@sortable` directive is registered by `SortableServiceProvider` and outputs a fully-formed `<a>` element with the correct icon.

```blade
<thead>
    <tr>
        <th>@sortable('id', '#')</th>
        <th>@sortable('name', 'Ονοματεπώνυμο')</th>
        <th>@sortable('created_at', 'Εγγραφή')</th>
    </tr>
</thead>
```

#### Directive signature

```
@sortable(column, title?, queryParams?, anchorAttributes?)
```

| Argument | Type | Description |
|----------|------|-------------|
| `column` | `string` | The column name to sort by (maps to `?sort=`) |
| `title` | `string\|null` | Link text. Omit to use the raw column name |
| `queryParams` | `array` | Extra key-value pairs merged into the query string |
| `anchorAttributes` | `array` | HTML attributes added to the `<a>` tag (e.g. `['class' => 'my-class']`). Use `'href'` to override the base URL |

#### Example with extra attributes

```blade
@sortable('name', 'Ονοματεπώνυμο', [], ['class' => 'text-warning'])
```

---

## Sorting by related-model columns (`applyRelationOrderBy`)

The `Sortable` trait supports sorting by a column that lives in a **related table** using dot-notation (e.g. `father.name`).  
When such a value is found in the model's `$sortable` array, the private `applyRelationOrderBy()` method is called automatically by `sortable()` — you never invoke it directly.

### How it works

1. The column string (e.g. `father.name`) is split into `$relation` and `$field`.
2. The model's `$hasOne` property is consulted to find the JOIN definition for `$relation`.
3. A `LEFT JOIN` is added to the query, aliased as `{relation}_{foreignTable}` (e.g. `father_parents`).
4. `{table}.*` is explicitly selected first, then `{alias}.{field} AS {relation}_{field}` (e.g. `father_parents.name AS father_name`) — this avoids the `SELECT expr, *` syntax error that MySQL rejects.
5. `ORDER BY {alias}.{field}` is appended.

### Defining `$hasOne` on your model

Two definition styles are supported:

#### Associative form (verbose, recommended)

```php
public array $hasOne = [
    'father' => [
        'foreign_table' => 'parents',   // the related table name
        'foreign_key'   => 'id',        // PK on the related table
        'local_key'     => 'father_id', // FK on THIS table
    ],
];
```

#### Short-form (positional array)

```php
public array $hasOne = [
    // [ ModelName, foreign_key_on_related_table, local_key_on_this_table ]
    'father' => ['ParentModel', 'id', 'father_id'],
];
```

When the short-form is used the trait calls `$this->load->model($modelName)` and reads `->table` from the loaded model instance to discover the foreign table name.

### Registering the relation column as sortable

Add the dot-notation key to the model's `$sortable` array:

```php
public array $sortable = [
    'id',
    'name',
    'father.name',   // ← dot-notation triggers applyRelationOrderBy
    'created_at',
];
```

### Full model example

```php
<?php

namespace App\Models;

use App\Core\MY_Model;
use Laraigniter\Sortable\Traits\Sortable;

class Child extends MY_Model
{
    use Sortable;

    public string $table = 'children';

    public array $sortable = [
        'id',
        'name',
        'father.name',
    ];

    public array $hasOne = [
        'father' => [
            'foreign_table' => 'parents',
            'foreign_key'   => 'id',
            'local_key'     => 'father_id',
        ],
    ];
}
```

Calling `(new Child())->sortable(['father.name' => 'asc'])->paginate(15)` will produce SQL similar to:

```sql
SELECT children.*, father_parents.name AS father_name
FROM children
LEFT JOIN parents AS father_parents ON father_parents.id = children.father_id
ORDER BY father_parents.name ASC
LIMIT 15
```

### Fallback behaviour

If `$hasOne` does not contain the requested relation, the method falls back to a plain `ORDER BY {table}.{relation}_{field}` (replacing `.` with `_`) rather than throwing an exception.

### Using the result column in a Blade view

Register the directive as usual — just use the dot-notation key as the `column` argument:

```blade
<th>@sortable('father.name', 'Father')</th>
```

`SortableLink` will pass `?sort=father.name` in the generated URL, which `sortable()` picks up on the next request.

---

## How `SortableState` works

`SortableState` is a static per-request store. When `sortable()` resolves a default sort (no `?sort=` in the URL), it calls:

```php
SortableState::set($column, $direction);
```

`SortableLink::render()` then reads this state to decide whether to show the active icon on the correct column — even on the very first page load when the URL carries no sort parameters.

---

## Internals — class map

| Class | Namespace | Responsibility |
|-------|-----------|----------------|
| `SortableServiceProvider` | `Laraigniter\Sortable` | Registers config path, `@sortable` directive, and publishable assets via the `post_controller_constructor` hook |
| `SortableLink` | `Laraigniter\Sortable` | Builds the complete `<a>` sort anchor HTML |
| `SortableState` | `Laraigniter\Sortable` | Static store for the active default sort column/direction |
| `Traits\Sortable` | `Laraigniter\Sortable\Traits` | Model trait — adds `sortable()`, `sortables()`, `getSortables()`, `hasSortable()` |
| `Traits\Sortable::applyRelationOrderBy()` | `Laraigniter\Sortable\Traits` | Private helper — resolves a dot-notation `relation.field` column into a LEFT JOIN + explicit SELECT + ORDER BY |

