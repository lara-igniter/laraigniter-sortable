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
| `default_icon_set` | `fal fa-sort` | Icon class used for unrecognised column types |
| `default_icon_type` | `default` | Fallback type key for suffix lookup |
| `sortable_icon` | `fal fa-sort fa-lg` | Icon shown when the column is not currently sorted |
| `clickable_icon` | `false` | When `true` the icon itself is wrapped inside the `<a>` |
| `icon_text_separator` | `<span class="ml-2"></span>` | HTML placed between link text and icon |
| `asc_*_suffix` | various `-down` / `-up` | Icon class suffix appended for ascending sorts |
| `desc_*_suffix` | various `-up` / `-up-alt` | Icon class suffix appended for descending sorts |
| `anchor_class` | `d-flex text-white` | CSS class on every sort `<a>` |
| `active_anchor_class` | `null` | Extra class added when the column is the active sort |
| `order_anchor_class_prefix` | `null` | Prefix for a dynamic direction class on the active anchor |
| `formatting_function` | `null` | Callable applied to the column title (e.g. `mb_strtoupper`) |
| `title_inside_anchor` | `false` | Use the raw column name as title when none is passed |
| `format_custom_titles` | `true` | Apply `formatting_function` to explicit titles too |
| `inject_title_as` | `null` | Query-string key to inject the formatted title into |
| `default_order` | `desc` | Direction used when no explicit order is provided |
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

class Member extends MY_Model
{
    use Sortable;

    protected $table = 'oc_members';

    // Declare which columns may be sorted.
    public array $sortable = [
        'id',
        'last_name',
        'registration_number',
        'created_at',
    ];
}
```

### 2. Call `->sortable()` in your repository / controller

```php
// Sorts by ?sort= & ?order= from the request.
// Falls back to config('sortable.default_first_column') when no params are present.
$members = $this->member->sortable()->paginate(15);

// Or pass an explicit default:
$members = $this->member->sortable('last_name')->paginate(15);

// Or pass a default direction too:
$members = $this->member->sortable(['created_at' => 'desc'])->paginate(15);
```

`sortable()` qualifies the column with the model's own table name automatically, so joins never produce ambiguous `ORDER BY` clauses.

### 3. Use `@sortable` in your Blade view

The `@sortable` directive is registered by `SortableServiceProvider` and outputs a fully-formed `<a>` element with the correct icon.

```blade
<thead>
    <tr>
        <th>@sortable('id', '#')</th>
        <th>@sortable('last_name', 'Επώνυμο')</th>
        <th>@sortable('registration_number', 'Αρ. Μητρώου')</th>
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
@sortable('last_name', 'Επώνυμο', [], ['class' => 'text-warning'])
```

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

