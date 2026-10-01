<?php

declare(strict_types=1);

namespace Marrow\Ui\Commands;

use Marrow\Console\Command;

/**
 * Wires Alpine.js into the skeleton's existing Vite pipeline
 * (resources/js/app.js, resources/css/app.css) — the only thing this
 * package can't just work out of the box for, since it's a frontend
 * dependency (`npm install`), not a Composer one.
 *
 * Registering components themselves needs no command at all: UiModule
 * does that on boot, the moment `composer require marrow/ui` runs.
 *
 *   php forge ui:install
 */
class UiInstallCommand extends Command
{
    protected string $signature = 'ui:install';
    protected string $description = 'Wire Alpine.js into resources/js/app.js and resources/css/app.css for the interactive components (Modal, Dropdown, dismissible Alert)';

    private const JS_MARKER = "import Alpine from 'alpinejs'";
    private const JS_SNIPPET = <<<'JS'
// Added by `php forge ui:install` (marrow/ui) — required by Modal, Dropdown,
// and dismissible Alert components (x-data/x-show/x-cloak/x-transition).
import Alpine from 'alpinejs'

window.Alpine = Alpine
Alpine.start()
JS;

    private const CSS_MARKER = '[x-cloak]';
    private const CSS_SNIPPET = <<<'CSS'
/* Added by `php forge ui:install` (marrow/ui) — hides x-cloak elements
   until Alpine has initialized, instead of flashing them open on load. */
[x-cloak] {
    display: none !important;
}
CSS;

    protected function handle(): int
    {
        $this->appendOnce(base_path('resources/js/app.js'), self::JS_MARKER, self::JS_SNIPPET, 'resources/js/app.js');
        $this->appendOnce(base_path('resources/css/app.css'), self::CSS_MARKER, self::CSS_SNIPPET, 'resources/css/app.css');

        $this->newLine();
        $this->success('Alpine.js wired into app.js/app.css.');
        $this->newLine();
        $this->line('   <fg=gray>Next:</>');
        $this->line('     npm install alpinejs');
        $this->line('     npm run dev   (or: npm run build)');

        return self::SUCCESS;
    }

    private function appendOnce(string $path, string $marker, string $snippet, string $label): void
    {
        if (!is_file($path)) {
            $this->warn("Could not find {$label} — add the Alpine.js setup there yourself (see this command's source).");
            return;
        }

        $contents = (string) file_get_contents($path);

        if (str_contains($contents, $marker)) {
            $this->warn("Already wired: {$label} (skipped).");
            return;
        }

        file_put_contents($path, rtrim($contents) . "\n\n" . $snippet . "\n");
        $this->success("Updated: {$label}");
    }
}
