<?php

namespace UvdeskAppCreator\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UvdeskAppCreator\Generator;
use Webkul\UVDesk\ExtensionFrameworkBundle\Console\BuildExtensions;

class CreatorCommand extends Command
{
    protected static $defaultName = 'uvdesk:app-creator';
    protected Generator $generator;

    public function __construct(private ContainerInterface $container)
    {
        parent::__construct($this->getName());
        $this->generator = new Generator($container);
    }

    protected function configure()
    {
        $this
            ->setHelp("
    php bin/console {$this->getName()} vendor/package
    php bin/console {$this->getName()} vendor/package -t
            ")
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the application. e.g.: vendor/package')
            ->addOption('has-table', 't', InputOption::VALUE_NONE, 'has table', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>AppCreatorBundle is ready!</info>');

        if (strpos($input->getArgument('name'), '/') === false) {
            throw new \RuntimeException('app name is not valid. e.g.: vendor/package');
        }
        if ($this->generator->checkRWX() === false) {
            throw new \RuntimeException('apps directory is not writable');
        }
        $this->process($input);

        $app = new Application();
        $app->add(new BuildExtensions($this->container))->run(new \Symfony\Component\Console\Input\ArrayInput([]), $output);
        $output->writeln('<info>Done!</info>');

        return Command::SUCCESS;
    }

    protected function process(InputInterface $input)
    {
        $this->generator->generate($input->getArguments(), $input->getOptions());
    }
}
