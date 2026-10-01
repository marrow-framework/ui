<?php

declare(strict_types=1);

namespace Marrow\Ui\Twig;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Compiles to plain calls against Twig's own public, stable API
 * (`Environment::getFunction()` / `TwigFunction::getCallable()`) rather
 * than hand-building a `FunctionExpression`/`CallExpression` node tree —
 * the latter would mean replicating `FunctionExpressionParser`'s argument-
 * resolution internals (named/positional argument matching against the
 * target callable's real signature), which are exactly that: internal, and
 * not something a package should couple itself to across Twig versions.
 */
final class ComponentNode extends Node
{
    public function __construct(Node $name, ?Node $props, Node $slot, int $lineno)
    {
        $nodes = ['name' => $name, 'slot' => $slot];
        if ($props !== null) {
            $nodes['props'] = $props;
        }

        parent::__construct($nodes, [], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $slotVar = $compiler->getVarName();

        $compiler
            ->write("\${$slotVar} = ")
            ->subcompile($this->getNode('slot'))
            ->raw("\n")
            ->write('echo ($this->env->getFunction(\'component\')->getCallable())(')
            ->subcompile($this->getNode('name'))
            ->raw(', array_merge(');

        if ($this->hasNode('props')) {
            $compiler->subcompile($this->getNode('props'));
        } else {
            $compiler->raw('[]');
        }

        // Cast to string rather than relying on Twig\Markup's __toString():
        // CaptureNode above yields a Markup instance (or '' for an empty
        // body), and a Component's $slot property is plainly typed
        // `string` — safer to coerce explicitly here than to depend on
        // PHP's object-to-typed-property coercion rules holding across
        // versions.
        $compiler->raw(", ['slot' => (string) \${$slotVar}]));\n");
    }
}
