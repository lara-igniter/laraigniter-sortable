<?php

namespace Laraigniter\Sortable;

use Elegant\Contracts\Support\Htmlable;

class SortableLink
{
    /**
     * @param array $parameters
     *
     * @return string
     */
    public static function render(array $parameters): string
    {
        [
            $sortColumn,
            $sortParameter,
            $title,
            $queryParameters,
            $anchorAttributes
        ] = self::parseParameters($parameters);

        $title = self::applyFormatting($title, $sortColumn);

        $mergeTitleAs = config('sortable.inject_title_as');

        if (!is_null($mergeTitleAs)) {
            request()->merge_get([$mergeTitleAs => $title]);
        }

        [$icon, $type, $order] = self::determineDirection($sortColumn, $sortParameter);

        $trailingTag = self::formTrailingTag($icon);

        $anchorClass = self::getAnchorClass($sortParameter, $anchorAttributes);

        $anchorAttributesString = self::buildAnchorAttributesString($anchorAttributes);

        $queryString = self::buildQueryString($queryParameters, $sortParameter, $order);

        $url = self::buildUrl($queryString, $anchorAttributes);

        return '<a' . $anchorClass . ' href="' . $url . '"' . $anchorAttributesString . '>' . e($title) . $trailingTag;
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public static function parseParameters(array $parameters): array
    {
        $sortColumn     = $parameters[0];
        $title          = (count($parameters) === 1) ? null : $parameters[1];
        $queryParameters  = (isset($parameters[2]) && is_array($parameters[2])) ? $parameters[2] : [];
        $anchorAttributes = (isset($parameters[3]) && is_array($parameters[3])) ? $parameters[3] : [];

        return [
            $sortColumn,
            $parameters[0],
            $title,
            $queryParameters,
            $anchorAttributes,
        ];
    }

    /**
     * @param string|Htmlable|null $title
     * @param string $sortColumn
     *
     * @return string|Htmlable|null
     */
    private static function applyFormatting($title, string $sortColumn)
    {
        if ($title instanceof Htmlable) {
            return $title;
        }

        if ($title === null && config('sortable.title_inside_anchor')) {
            $title = $sortColumn;
        } elseif (!config('sortable.format_custom_titles')) {
            return $title;
        }

        $formatting_function = config('sortable.formatting_function') ?? null;

        if (!is_null($formatting_function) && function_exists($formatting_function)) {
            $title = call_user_func($formatting_function, $title);
        }

        return $title;
    }

    /**
     * @param string $sortColumn
     * @param string $sortParameter
     *
     * @return array
     */
    private static function determineDirection(string $sortColumn, string $sortParameter): array
    {
        [$icon, $type] = self::selectIcon($sortColumn);

        if (request()->get('sort') == $sortParameter && in_array(request()->get('order'), ['asc', 'desc'])) {
            // Active via URL ?sort=&order= parameters.
            $icon .= (request()->get('order') === 'asc'
                ? config('sortable.asc_' . $type . '_suffix') ?? '-up'
                : config('sortable.desc_' . $type . '_suffix') ?? '-down');
            $order = request()->get('order') === 'desc' ? 'asc' : 'desc';

        } elseif (!request()->has('sort') && SortableState::isActive($sortParameter)) {
            // Active via default sort set by ->sortable([column => direction]).
            $defaultDir = SortableState::direction();
            $icon      .= ($defaultDir === 'asc'
                ? config('sortable.asc_' . $type . '_suffix') ?? '-up'
                : config('sortable.desc_' . $type . '_suffix') ?? '-down');
            $order = $defaultDir === 'desc' ? 'asc' : 'desc';

        } else {
            $icon  = config('sortable.sortable_icon');
            $order = config('sortable.default_order_unsorted') ?? 'asc';
        }

        return [$icon, $type, $order];
    }

    /**
     * @param string $sortColumn
     *
     * @return array
     */
    private static function selectIcon(string $sortColumn): array
    {
        $icon = config('sortable.default_icon_set');
        $type = config('sortable.default_icon_type');

        if (!empty(config('sortable.columns'))) {
            foreach (config('sortable.columns') as $key => $value) {
                if (in_array($sortColumn, $value['rows'])) {
                    $icon = $value['class'];
                    $type = $key;
                }
            }
        }

        return [$icon, $type];
    }

    /**
     * @param string $icon
     *
     * @return string
     */
    private static function formTrailingTag(string $icon): string
    {
        $enableIcons = config('sortable.enable_icons') ?? true;

        if (!$enableIcons) {
            return '</a>';
        }

        $iconAndTextSeparator = config('sortable.icon_text_separator') ?? '';

        $clickableIcon = config('sortable.clickable_icon') ?? false;

        $trailingTag = $iconAndTextSeparator . '<i class="' . $icon . '"></i>' . '</a>';

        if ($clickableIcon === false) {
            return '</a>' . $iconAndTextSeparator . '<i class="' . $icon . '"></i>';
        }

        return $trailingTag;
    }

    /**
     * Take care of a special case, when `class` is passed to the sortable link.
     *
     * @param string $sortColumn
     * @param array  $anchorAttributes
     *
     * @return string
     */
    private static function getAnchorClass(string $sortColumn, array &$anchorAttributes = []): string
    {
        $class = [];

        $anchorClass = config('sortable.anchor_class') ?? null;

        if ($anchorClass !== null) {
            $class[] = $anchorClass;
        }

        $activeClass = config('sortable.active_anchor_class') ?? null;

        if ($activeClass !== null && self::shouldShowActive($sortColumn)) {
            $class[] = $activeClass;
        }

        $orderClassPrefix = config('sortable.order_anchor_class_prefix') ?? null;

        if ($orderClassPrefix !== null && self::shouldShowActive($sortColumn)) {
            $class[] = $orderClassPrefix . (request()->get('order') === 'asc'
                    ? config('sortable.asc_suffix') ?? '-up'
                    : config('sortable.desc_suffix') ?? '-down');
        }

        if (isset($anchorAttributes['class'])) {
            $class = array_merge($class, explode(' ', $anchorAttributes['class']));

            unset($anchorAttributes['class']);
        }

        return (empty($class)) ? '' : ' class="' . implode(' ', $class) . '"';
    }

    /**
     * @param string $sortColumn
     *
     * @return bool
     */
    private static function shouldShowActive(string $sortColumn): bool
    {
        if (request()->has('sort')) {
            return request()->get('sort') == $sortColumn;
        }

        return SortableState::isActive($sortColumn);
    }

    /**
     * @param array $anchorAttributes
     *
     * @return string
     */
    private static function buildAnchorAttributesString(array $anchorAttributes): string
    {
        if (empty($anchorAttributes)) {
            return '';
        }

        unset($anchorAttributes['href']);

        $attributes = [];
        foreach ($anchorAttributes as $k => $v) {
            $attributes[] = $k . ('' != $v ? '="' . $v . '"' : '');
        }

        return ' ' . implode(' ', $attributes);
    }

    /**
     * @param array  $queryParameters
     * @param string $sortParameter
     * @param string $order
     *
     * @return string
     */
    private static function buildQueryString(array $queryParameters, string $sortParameter, string $order): string
    {
        $checkStrlenOrArray = function ($element) {
            return is_array($element) ? $element : strlen($element);
        };

        $persistParameters = array_filter(
            collect(request()->get())->except(['sort', 'order'])->toArray(),
            $checkStrlenOrArray
        );

        return http_build_query(array_merge($queryParameters, $persistParameters, [
            'sort'  => $sortParameter,
            'order' => $order,
        ]));
    }

    /**
     * @param string $queryString
     * @param array  $anchorAttributes
     *
     * @return string
     */
    private static function buildUrl(string $queryString, array $anchorAttributes): string
    {
        if (!isset($anchorAttributes['href'])) {
            return site_url(app('route')->getFullPath() . '?' . $queryString);
        }

        return site_url($anchorAttributes['href'] . '?' . $queryString);
    }
}

