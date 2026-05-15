<?php

namespace UvdeskAppCreator;

use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Generator
{
    protected string $name;
    protected bool $hasTable;
    protected array $config;


    public function __construct(private ContainerInterface $container)
    {
    }

    public function getQualifiedName() : string
    {
        return str_replace(['/', '-'], '_', $this->name);
    }

    public function getNamespace($dirname = '') : string
    {
        [$vendor, $package] = explode('/', $this->name);
        $prefix = Str::asCamelCase($vendor) .'\\'. Str::asCamelCase(str_replace('-', ' ', $package));
        return $dirname ? $prefix .'\\'. $dirname : $prefix;
    }

    public function getClassName() : string
    {
        $package = explode('/', $this->name)[1];
        return Str::asClassName(str_replace('-', ' ', $package));
    }

    public function checkRWX(): bool
    {
        $realPath = $this->container->get('kernel')->getProjectDir() . '/apps';
        if (file_exists($realPath)) {
            return is_writable($realPath);
        }
        return is_writable(dirname($realPath));
    }

    public function generate($params, $options=[]): void
    {
        $this->name = $params['name'];
        $this->hasTable = $options['has-table'] ?? false;
        $this->config = [
            '{{name}}' => $this->name,
            '{{qualifiedName}}' => $this->getQualifiedName(),
            '{{namespace}}' => $this->getNamespace(),
            '{{className}}' => $this->getClassName(),
        ];
        $this->application();
        $this->console();
        $this->controller();
        $this->dependency();
        $this->entity();
        $this->resource();
        $this->service();
    }

    protected function write($filename, $content): void
    {
        $realPath = $this->container->get('kernel')->getProjectDir() . '/apps/' . $this->name . '/' . $filename;
        if (! is_dir(dirname($realPath))) {
            mkdir(dirname($realPath), 0755, true);
        }
        file_put_contents($realPath, $content);
    }

    protected function replace($filename, $config=[]): string
    {
        $config = array_merge($this->config, $config);
        $content = file_get_contents(__DIR__ . '/Templates/'. $filename);
        $content = str_replace(array_keys($config), array_values($config), $content);
        return $content;
    }

    protected function application(): void
    {
        // json, package, application
        $this->write('extension.json', $this->replace('application-json.template', ['{{namespace}}' => str_replace('\\', '\\\\', $this->getNamespace())]));
        $this->write('src/'. $this->getClassName() .'Package.php', $this->replace('application-package.template'));
        $this->write('src/Application/'. $this->getClassName() .'Application.php', $this->replace('application.template'));
        $this->write('src/Application/ApplicationMetadata.php', $this->replace('application-metadata.template'));
    }

    protected function console(): void
    {
        $this->write('src/Console/.gitignore', '');
    }

    protected function controller(): void
    {
        $this->write('src/Controller/BaseController.php', $this->replace('controller-base.template'));
        $this->write('src/Controller/SettingsController.php', $this->replace('controller-settings.template'));
    }

    protected function dependency(): void
    {
        $this->write('src/DependencyInjection/Configuration.php', $this->replace('dependency.template'));
    }

    protected function entity(): void
    {
        // entity, repository
        if ($this->hasTable) {
            $this->write('src/Entity/.gitignore', '');
            $this->write('src/Repository/.gitignore', '');
        }
    }

    protected function resource(): void
    {
        // resource, routing
        $this->write('src/Routing/RoutingResource.php', $this->replace('resource-routing.template'));
        $this->write('src/Resources/views/dashboard.html.twig', $this->replace('resource-view.template'));
        $this->write('src/Resources/config/services.yaml', $this->replace('resource-config.template'));
        $this->write('src/Resources/config/extension.yaml', $this->getQualifiedName() .":\n    name: '". $this->name ."'\n    enabled: false\n");
        $this->write('src/Resources/config/routes.yaml', $this->getQualifiedName() ."_app:\n    resource: 'routes/*'\n    prefix: /{_locale}/%uvdesk_site_path.member_prefix%/apps/{$this->name}/{$this->getQualifiedName()}\n");
        $this->write('src/Resources/config/routes/public.yaml', $this->replace('resource-routes.template'));
    }

    protected function service(): void
    {
        $this->write('src/Service/.gitignore', '');
    }
}