<?php

declare(strict_types=1);

namespace Marrow\Ui\Commands;

use Marrow\Console\Command;

/**
 * Wires Alpine.js into the skeleton's existing Vite pipeline
 * (resources/js/app.js, resources/css/app.css), and tells Tailwind v4 to
 * actually scan this package's own Twig templates for utility classes —
 * neither is something this package can just work out of the box for,
 * since both are frontend build-pipeline concerns (`npm install`/CSS),
 * not something a Composer package can hook into on its own.
 *
 * The Tailwind piece matters a lot more than it looks: Tailwind v4's
 * automatic content detection skips anything `.gitignore` excludes, and
 * every app's `.gitignore` excludes `/vendor/` — so without an explicit
 * `@source` pointing at this package's own installed path, every utility
 * class that appears *only* inside marrow/ui's templates (a button's
 * `bg-orange-500`, a checkbox's `h-4 w-4 rounded ...`, an input's
 * `rounded-lg border bg-slate-900 ...`) is silently never generated —
 * the component still renders, just with none of its own styling, which
 * is easy to mistake for "the design is just plain" rather than "the CSS
 * for this is missing entirely".
 *
 * Registering components themselves needs no command at all: UiModule
 * does that on boot, the moment `composer require marrow/ui` runs.
 *
 *   php forge ui:install
 */
class UiInstallCommand extends Command
{
    protected string $signature = 'ui:install';
    protected string $description = 'Wire Alpine.js (resources/js/app.js) and a Tailwind @source for this package\'s own templates (resources/css/app.css) into the Vite pipeline';

    private const JS_MARKER = "import Alpine from 'alpinejs'";
    private const JS_SNIPPET = <<<'JS'
// Added by `php forge ui:install` (marrow/ui) — required by Modal, Dropdown,
// and dismissible Alert components (x-data/x-show/x-cloak/x-transition).
import Alpine from 'alpinejs'

window.Alpine = Alpine
Alpine.start()
JS;

    private const CLOAK_MARKER = '[x-cloak]';
    private const CLOAK_SNIPPET = <<<'CSS'
/* Added by `php forge ui:install` (marrow/ui) — hides x-cloak elements
   until Alpine has initialized, instead of flashing them open on load. */
[x-cloak] {
    display: none !important;
}
CSS;

    private const SOURCE_MARKER = '@source "../../vendor/marrow/ui/src/Views"';
    private const SOURCE_SNIPPET = <<<'CSS'
/* Added by `php forge ui:install` (marrow/ui) — Tailwind v4 skips anything
   .gitignore excludes (which includes /vendor/) during its automatic
   content scan, so without this, every utility class that appears only
   inside this package's own templates is never generated. Must come
   after `@import "tailwindcss"` for Tailwind to see it. */
@source "../../vendor/marrow/ui/src/Views";
CSS;

    protected function handle(): int
    {
        $this->appendOnce(base_path('resources/js/app.js'), self::JS_MARKER, self::JS_SNIPPET, 'resources/js/app.js');
        $this->appendOnce(base_path('resources/css/app.css'), self::CLOAK_MARKER, self::CLOAK_SNIPPET, 'resources/css/app.css (x-cloak)');
        $this->appendOnce(base_path('resources/css/app.css'), self::SOURCE_MARKER, self::SOURCE_SNIPPET, 'resources/css/app.css (@source)');

        $this->newLine();
        $this->success('Alpine.js wired into app.js/app.css, Tailwind @source added for this package\'s templates.');
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
