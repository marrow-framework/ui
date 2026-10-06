# Changelog

All notable changes to this project are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.1.1] - 2026-10-06

### Fixed

- **Every component rendered with none of its own styling** — `php forge ui:install` wired Alpine.js into
  `resources/js/app.js`/`resources/css/app.css` but never told Tailwind v4 to scan this package's own templates
  for utility classes. Tailwind v4's automatic content detection skips anything `.gitignore` excludes, which
  includes `/vendor/` in every Marrow app — so a utility class that appears *only* inside `marrow/ui`'s own
  Twig templates (a button's `bg-orange-500`, a checkbox's `h-4 w-4 rounded ...`, an input's `rounded-lg border
  bg-slate-900 ...`) was silently never generated, even though the component itself rendered fine. `ui:install`
  now also appends `@source "../../vendor/marrow/ui/src/Views";` to `resources/css/app.css`. An app that already
  ran `ui:install` before this fix needs that one line added by hand (or re-run `ui:install` — already-wired
  sections are skipped, so it's safe to re-run).

## [1.1.0] - 2026-10-05

### Added

- **Every component now accepts `class` (merged, additive) and `attrs` (arbitrary HTML attribute passthrough)** —
  previously only 28/33 components exposed `class`, and only `input`/`textarea`/`field` exposed `attrs`, so most
  components had no way to receive a `style=""`, `data-*`, `aria-*`, or any other HTML attribute with no
  dedicated prop. `DropdownComponent`, `ModalComponent`, `Select\SelectItemComponent`, `ToasterComponent`, and
  `TooltipComponent` gained `class`; every component except `input`, `textarea`, `field`, and `form` (unchanged)
  gained `attrs`. `attrs` follows the same convention already established by `InputComponent`/`TextareaComponent`:
  a `true` value renders a bare boolean attribute (`required: true` → `required`), `false`/`null` omits the
  attribute entirely, anything else renders `name="value"`. The rendering loop itself was extracted out of
  `input.html.twig`/`textarea.html.twig` into a shared macro, `Views/macros/attrs.html.twig`, now reused by
  every component template instead of being duplicated. For a component with both a visible panel and
  invisible Alpine plumbing (`modal`, `dropdown`), `class`/`attrs` target the visible panel, not the `x-data`
  wrapper. For `checkbox`/`radio`/`switch`, `attrs` targets the underlying `<input>` (so `required`,
  `autocomplete`, `aria-describedby`, etc. land on the actual form control), while `class` stays on the outer
  `<label>` as before (layout/spacing of the label row).
  Fully backward compatible: both new props default to `''`/`[]` and change nothing for existing callers that
  don't pass them.

### Fixed

- **`Select\SelectComponent` silently dropped an `attrs` prop forwarded to it** — `Form\FieldComponent` has
  always forwarded `attrs` to the underlying `select` component for a field with `options`, but
  `SelectComponent` never declared the prop nor rendered it, so it had no effect. Now declared and rendered
  like every other form control.
