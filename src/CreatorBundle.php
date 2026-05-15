<?php

namespace UvdeskAppCreator;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use UvdeskAppCreator\Command\CreatorCommand;

class CreatorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    public function registerCommands(Application $application)
    {
        $application->add(new CreatorCommand($this->container));
    }
}
