<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {{ component('pagination', {currentPage: 3, lastPage: 12, urlPattern: '/posts?page={page}'}) }}
 *
 * `urlPattern` must contain a literal `{page}` placeholder — the component
 * only ever substitutes a page number into it, never builds a URL from a
 * route name itself (that would need a Router instance this presentational
 * class deliberately has no dependency on).
 */
class PaginationComponent extends Component
{
    public int $currentPage = 1;
    public int $lastPage = 1;
    public string $urlPattern = '?page={page}';
    /** How many page numbers to show on each side of the current page. */
    public int $siblings = 1;
    public string $class = '';
    public array $attrs = [];

    public function render(): string
    {
        return '@ui/components/pagination';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'pages' => $this->buildPageList(),
            'prevUrl' => $this->currentPage > 1 ? $this->urlFor($this->currentPage - 1) : null,
            'nextUrl' => $this->currentPage < $this->lastPage ? $this->urlFor($this->currentPage + 1) : null,
        ]);
    }

    /** @return array<int, array{page: int, url: string}|array{ellipsis: true}> */
    private function buildPageList(): array
    {
        $pages = [];
        $start = max(1, $this->currentPage - $this->siblings);
        $end = min($this->lastPage, $this->currentPage + $this->siblings);

        if ($start > 1) {
            $pages[] = ['page' => 1, 'url' => $this->urlFor(1)];
            if ($start > 2) {
                $pages[] = ['ellipsis' => true];
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = ['page' => $i, 'url' => $this->urlFor($i)];
        }

        if ($end < $this->lastPage) {
            if ($end < $this->lastPage - 1) {
                $pages[] = ['ellipsis' => true];
            }
            $pages[] = ['page' => $this->lastPage, 'url' => $this->urlFor($this->lastPage)];
        }

        return $pages;
    }

    private function urlFor(int $page): string
    {
        return str_replace('{page}', (string) $page, $this->urlPattern);
    }
}
