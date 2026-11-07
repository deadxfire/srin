<?php

namespace Core;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use ArrayIterator;
use Traversable;

/**
 * ============================================================================
 * Srin Engine - Responsive Database Paginator
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
class Paginator implements IteratorAggregate, Countable, ArrayAccess, JsonSerializable
{
    protected array $items;
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected int $lastPage;
    protected string $pageName;
    protected string $path = '/';
    protected array $query = [];

    public function __construct(
        array $items,
        int $total,
        int $perPage = 15,
        int $currentPage = 1,
        string $pageName = 'page'
    ) {
        $this->items = $items;
        $this->total = max(0, $total);
        $this->perPage = max(1, $perPage);
        $this->lastPage = max(1, (int) ceil($this->total / $this->perPage));
        $this->currentPage = max(1, min($currentPage, $this->lastPage));
        $this->pageName = $pageName;

        // Auto-detect current path and query parameters
        $this->path = Request::uri();
        $this->query = $_GET ?? [];
    }

    /**
     * Get the slice of items being paginated.
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get the total number of items in the dataset.
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get the number of items shown per page.
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the current page number.
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get the last available page number.
     */
    public function lastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     */
    public function hasPages(): bool
    {
        return $this->total > $this->perPage;
    }

    /**
     * Determine if the paginator is on the first page.
     */
    public function onFirstPage(): bool
    {
        return $this->currentPage <= 1;
    }

    /**
     * Determine if there are more pages beyond the current one.
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    /**
     * Get the index of the first item on current page (1-based).
     */
    public function firstItem(): ?int
    {
        return count($this->items) > 0 ? ($this->currentPage - 1) * $this->perPage + 1 : null;
    }

    /**
     * Get the index of the last item on current page (1-based).
     */
    public function lastItem(): ?int
    {
        return count($this->items) > 0 ? $this->firstItem() + count($this->items) - 1 : null;
    }

    /**
     * Set the base URL path for page links.
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Append query string values to pagination links (e.g. search, filters).
     */
    public function appends(array|string $key, mixed $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->query[$k] = $v;
            }
        } else {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Get URL for a given page number.
     */
    public function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        $parameters = array_merge($this->query, [$this->pageName => $page]);
        $queryString = http_build_query($parameters);

        return $this->path . ($queryString !== '' ? '?' . $queryString : '');
    }

    /**
     * Get the URL for the previous page, or null if on first page.
     */
    public function previousPageUrl(): ?string
    {
        return $this->currentPage > 1 ? $this->url($this->currentPage - 1) : null;
    }

    /**
     * Get the URL for the next page, or null if on last page.
     */
    public function nextPageUrl(): ?string
    {
        return $this->hasMorePages() ? $this->url($this->currentPage + 1) : null;
    }

    /**
     * Render the pagination navigation HTML.
     */
    public function links(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav class="srin-pagination-wrapper" aria-label="Pagination Navigation">';
        $html .= '<style>
            .srin-pagination-wrapper { margin: 24px 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-family: inherit; }
            .srin-pagination-info { font-size: 0.875rem; color: #64748b; }
            .srin-pagination { display: inline-flex; list-style: none; padding: 0; margin: 0; border-radius: 8px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
            .srin-page-item { display: inline-block; border-right: 1px solid #e2e8f0; }
            .srin-page-item:last-child { border-right: none; }
            .srin-page-link { display: flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 12px; font-size: 0.875rem; color: #334155; text-decoration: none; transition: background-color 0.15s ease, color 0.15s ease; }
            .srin-page-link:hover { background-color: #f1f5f9; color: #0284c7; }
            .srin-page-item.active .srin-page-link { background-color: #0284c7; color: #ffffff; font-weight: 600; cursor: default; }
            .srin-page-item.disabled .srin-page-link { color: #94a3b8; pointer-events: none; background: #fafafa; cursor: not-allowed; }
            @media (prefers-color-scheme: dark) {
                .srin-pagination { background: #1e293b; border-color: #334155; }
                .srin-page-item { border-color: #334155; }
                .srin-page-link { color: #cbd5e1; }
                .srin-page-link:hover { background-color: #334155; color: #38bdf8; }
                .srin-page-item.active .srin-page-link { background-color: #0284c7; color: #ffffff; }
                .srin-page-item.disabled .srin-page-link { color: #475569; background: #0f172a; }
                .srin-pagination-info { color: #94a3b8; }
            }
        </style>';

        $html .= '<div class="srin-pagination-info">';
        $html .= 'Showing <strong>' . $this->firstItem() . '</strong> to <strong>' . $this->lastItem() . '</strong> of <strong>' . $this->total() . '</strong> results';
        $html .= '</div>';

        $html .= '<ul class="srin-pagination">';

        // Previous button
        if ($this->onFirstPage()) {
            $html .= '<li class="srin-page-item disabled"><span class="srin-page-link" aria-hidden="true">&laquo; Previous</span></li>';
        } else {
            $html .= '<li class="srin-page-item"><a class="srin-page-link" href="' . htmlspecialchars($this->previousPageUrl(), ENT_QUOTES, 'UTF-8') . '" rel="prev">&laquo; Previous</a></li>';
        }

        // Smart Page Number Generation with ellipsis (...)
        $window = 2; // number of pages around current page
        $elements = [];

        if ($this->lastPage <= ($window * 2 + 5)) {
            for ($i = 1; $i <= $this->lastPage; $i++) {
                $elements[] = $i;
            }
        } else {
            $elements[] = 1;
            if ($this->currentPage > ($window + 2)) {
                $elements[] = '...';
            }

            $start = max(2, $this->currentPage - $window);
            $end   = min($this->lastPage - 1, $this->currentPage + $window);

            for ($i = $start; $i <= $end; $i++) {
                $elements[] = $i;
            }

            if ($this->currentPage < ($this->lastPage - $window - 1)) {
                $elements[] = '...';
            }
            $elements[] = $this->lastPage;
        }

        foreach ($elements as $el) {
            if ($el === '...') {
                $html .= '<li class="srin-page-item disabled"><span class="srin-page-link">&hellip;</span></li>';
            } elseif ($el === $this->currentPage) {
                $html .= '<li class="srin-page-item active" aria-current="page"><span class="srin-page-link">' . $el . '</span></li>';
            } else {
                $html .= '<li class="srin-page-item"><a class="srin-page-link" href="' . htmlspecialchars($this->url((int) $el), ENT_QUOTES, 'UTF-8') . '">' . $el . '</a></li>';
            }
        }

        // Next button
        if ($this->hasMorePages()) {
            $html .= '<li class="srin-page-item"><a class="srin-page-link" href="' . htmlspecialchars($this->nextPageUrl(), ENT_QUOTES, 'UTF-8') . '" rel="next">Next &raquo;</a></li>';
        } else {
            $html .= '<li class="srin-page-item disabled"><span class="srin-page-link" aria-hidden="true">Next &raquo;</span></li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }

    /**
     * String representation (returns rendered HTML links).
     */
    public function __toString(): string
    {
        return $this->links();
    }

    /**
     * IteratorAggregate implementation.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Countable implementation.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * ArrayAccess implementation.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * JsonSerializable implementation.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert paginator instance to an array.
     */
    public function toArray(): array
    {
        $serializedData = array_map(function ($item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                return $item->toArray();
            }
            return (array) $item;
        }, $this->items);

        return [
            'current_page'   => $this->currentPage,
            'data'           => $serializedData,
            'first_page_url' => $this->url(1),
            'from'           => $this->firstItem(),
            'last_page'      => $this->lastPage,
            'last_page_url'  => $this->url($this->lastPage),
            'next_page_url'  => $this->nextPageUrl(),
            'path'           => $this->path,
            'per_page'       => $this->perPage,
            'prev_page_url'  => $this->previousPageUrl(),
            'to'             => $this->lastItem(),
            'total'          => $this->total,
        ];
    }

    /**
     * Convert paginator to JSON string.
     */
    public function toJson(int $options = JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toArray(), $options);
    }
}

