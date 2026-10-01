<?php

declare(strict_types=1);

namespace Marrow\Ui\Twig;

use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * Decorates the Engine's existing Twig loader (whatever it is — namespaces
 * and all) to rewrite `<mu-name>` tags before Twig's own lexer ever sees a
 * template's source. Caching/freshness checks (getCacheKey/isFresh) delegate
 * straight through to the inner loader unchanged: they're mtime/identity
 * based, not content based, so wrapping getSourceContext() alone is enough —
 * editing a .twig file still invalidates the compiled cache exactly as
 * before, and the rewrite simply reruns against the new content next time.
 */
final class HtmlComponentLoader implements LoaderInterface
{
    public function __construct(private readonly LoaderInterface $inner)
    {
    }

    public function getSourceContext(string $name): Source
    {
        $source = $this->inner->getSourceContext($name);
        $rewritten = HtmlComponentTag::rewrite($source->getCode());

        return new Source($rewritten, $source->getName(), $source->getPath());
    }

    public function getCacheKey(string $name): string
    {
        return $this->inner->getCacheKey($name);
    }

    public function isFresh(string $name, int $time): bool
    {
        return $this->inner->isFresh($name, $time);
    }

    public function exists(string $name): bool
    {
        return $this->inner->exists($name);
    }
}
