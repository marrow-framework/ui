<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Card;

use Marrow\Template\Component;

/** See CardHeaderComponent's docblock for the compositional card family. */
class CardFooterComponent extends Component
{
    public string $class = '';
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/card/card-footer';
    }
}
