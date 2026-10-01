<?php

declare(strict_types=1);

namespace Marrow\Ui;

use Marrow\Module\Attributes\Module;
use Marrow\Module\BaseModule;
use Marrow\Template\ComponentRegistry;
use Marrow\Ui\Commands\UiInstallCommand;
use Marrow\Ui\Components\AccordionComponent;
use Marrow\Ui\Components\AlertComponent;
use Marrow\Ui\Components\AvatarComponent;
use Marrow\Ui\Components\BadgeComponent;
use Marrow\Ui\Components\Breadcrumb\BreadcrumbComponent;
use Marrow\Ui\Components\Breadcrumb\BreadcrumbItemComponent;
use Marrow\Ui\Components\ButtonComponent;
use Marrow\Ui\Components\Card\CardComponent;
use Marrow\Ui\Components\Card\CardContentComponent;
use Marrow\Ui\Components\Card\CardDescriptionComponent;
use Marrow\Ui\Components\Card\CardFooterComponent;
use Marrow\Ui\Components\Card\CardHeaderComponent;
use Marrow\Ui\Components\Card\CardTitleComponent;
use Marrow\Ui\Components\CheckboxComponent;
use Marrow\Ui\Components\DropdownComponent;
use Marrow\Ui\Components\Form\FieldComponent;
use Marrow\Ui\Components\Form\FormComponent;
use Marrow\Ui\Components\InputComponent;
use Marrow\Ui\Components\LabelComponent;
use Marrow\Ui\Components\ModalComponent;
use Marrow\Ui\Components\PaginationComponent;
use Marrow\Ui\Components\ProgressComponent;
use Marrow\Ui\Components\RadioComponent;
use Marrow\Ui\Components\Select\SelectComponent;
use Marrow\Ui\Components\Select\SelectItemComponent;
use Marrow\Ui\Components\SeparatorComponent;
use Marrow\Ui\Components\SkeletonComponent;
use Marrow\Ui\Components\SpinnerComponent;
use Marrow\Ui\Components\SwitchComponent;
use Marrow\Ui\Components\TableComponent;
use Marrow\Ui\Components\TabsComponent;
use Marrow\Ui\Components\TextareaComponent;
use Marrow\Ui\Components\ToasterComponent;
use Marrow\Ui\Components\TooltipComponent;
use Marrow\Ui\Notifications\ToastNotificationSource;
use Marrow\Ui\Twig\ComponentTokenParser;
use Marrow\Ui\Twig\HtmlComponentLoader;
use Twig\TwigFunction;

/**
 * Registers this package's components into the framework's own
 * ComponentRegistry (a singleton — see Application::bindCoreServices()),
 * and the `@ui` Twig namespace their templates live under.
 *
 * Unlike marrow/warden, nothing here is published/copied into the app:
 * `composer require marrow/ui` is the whole install, same as
 * marrow/form-builder. Use a component straight away:
 *
 *   {{ component('button', {variant: 'primary', slot: 'Save'}) }}
 *
 * Components with sub-pieces (Card, Breadcrumb, Select, Form) group their
 * PHP classes under a `Components/{Family}/` folder and their templates
 * under a matching `Views/components/{family}/` folder — e.g.
 * `Components/Card/CardHeaderComponent.php` renders
 * `Views/components/card/card-header.html.twig`. A standalone component
 * with no sub-pieces (Button, Modal, ...) stays flat in `Components/` /
 * `Views/components/`. Registered component *names* (the string passed to
 * `component('name', ...)`) are unaffected either way — `card-header` is
 * `card-header` regardless of which folder its class lives in.
 *
 * Overriding a component's markup doesn't require forking the package —
 * register your own class under the same name instead:
 *
 *   $registry->registerAs('button', \Modules\Ui\MyButtonComponent::class);
 */
#[Module(name: 'ui', commands: [UiInstallCommand::class])]
class UiModule extends BaseModule
{
    private const COMPONENTS = [
        ButtonComponent::class,
        AlertComponent::class,
        BadgeComponent::class,
        ModalComponent::class,
        DropdownComponent::class,
        InputComponent::class,
        TextareaComponent::class,
        LabelComponent::class,
        CheckboxComponent::class,
        RadioComponent::class,
        SwitchComponent::class,
        SeparatorComponent::class,
        AvatarComponent::class,
        SpinnerComponent::class,
        ProgressComponent::class,
        SkeletonComponent::class,
        TooltipComponent::class,
        TabsComponent::class,
        AccordionComponent::class,
        PaginationComponent::class,
        TableComponent::class,
        ToasterComponent::class,
        // Card family
        CardComponent::class,
        CardHeaderComponent::class,
        CardTitleComponent::class,
        CardDescriptionComponent::class,
        CardContentComponent::class,
        CardFooterComponent::class,
        // Breadcrumb family
        BreadcrumbComponent::class,
        BreadcrumbItemComponent::class,
        // Select family
        SelectComponent::class,
        SelectItemComponent::class,
        // Form family
        FieldComponent::class,
        FormComponent::class,
    ];

    public function boot(): void
    {
        $this->registerViewNamespace('ui', $this->path('Views'));

        $registry = $this->container->make(ComponentRegistry::class);
        foreach (self::COMPONENTS as $component) {
            // A registerAs() override registered by an earlier-booted module
            // wins — never clobber it back to the package default.
            if (!$registry->has($component::componentName())) {
                $registry->register($component);
            }
        }

        // Backs the `toaster` component's link to NotificationManager's
        // `database` channel — see ToastNotificationSource. A plain Twig
        // function (Engine::getTwig() is public precisely for this: a
        // package registering its own Twig function/tag without needing a
        // core change) rather than another component, since it returns
        // data for the template to loop over, not markup to render.
        $this->getView()->getTwig()->addFunction(new TwigFunction(
            'toast_notifications',
            fn (): array => $this->container->make(ToastNotificationSource::class)->pull(),
        ));

        // `{% component 'name' with {...} %}...{% endcomponent %}` — see
        // Twig\ComponentTokenParser's docblock for what it saves you from
        // writing by hand, and its scope (single default slot).
        $this->getView()->getTwig()->addTokenParser(new ComponentTokenParser());

        // `<mui-button variant="primary">Save</mui-button>` — rewritten to the
        // tag form above before Twig's lexer ever runs on it. See
        // Twig\HtmlComponentTag's docblock for the attribute syntax and its
        // accepted limitations (no awareness of Twig comments/verbatim
        // blocks — don't use the `<mui-` prefix for anything else).
        $twig = $this->getView()->getTwig();
        $twig->setLoader(new HtmlComponentLoader($twig->getLoader()));
    }
}
