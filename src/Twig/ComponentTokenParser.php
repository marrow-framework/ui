<?php

declare(strict_types=1);

namespace Marrow\Ui\Twig;

use Twig\Node\CaptureNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Sugar over the existing `{{ component(name, props) }}` function call:
 *
 *   {% component 'card' with {title: 'Account'} %}
 *       <p>Some rich content here.</p>
 *   {% endcomponent %}
 *
 * Equivalent to, and compiles down to roughly the same thing as, writing it
 * out by hand:
 *
 *   {% set body %}<p>Some rich content here.</p>{% endset %}
 *   {{ component('card', {title: 'Account', slot: body}) }}
 *
 * — the tag's body is captured via Twig's own `CaptureNode` (the exact
 * mechanism `{% set x %}...{% endset %}` already uses internally, see
 * SetNode) and merged into `props` under the `slot` key, so there's no
 * `{% set %}` to write by hand and no variable name to invent.
 *
 * `with {...}` is optional — `{% component 'badge' %}Active{% endcomponent %}`
 * works with no props at all.
 *
 * Single default slot only. A component needing more than one named slot
 * (Modal's `trigger` + `slot`, Dropdown's `trigger` + `slot`) isn't served
 * by this tag — see this package's README for why `{% embed %}` (plain,
 * native Twig, no custom parser needed) is the better fit for those
 * instead of a second, more elaborate tag with nested named blocks.
 */
final class ComponentTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $name = $this->parser->getExpressionParser()->parseExpression();

        $props = null;
        if ($stream->test(Token::NAME_TYPE, 'with')) {
            $stream->next();
            $props = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse([$this, 'decideComponentEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return new ComponentNode($name, $props, new CaptureNode($body, $lineno), $lineno);
    }

    public function decideComponentEnd(Token $token): bool
    {
        return $token->test('endcomponent');
    }

    public function getTag(): string
    {
        return 'component';
    }
}
