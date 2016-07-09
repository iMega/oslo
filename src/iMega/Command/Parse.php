<?php

namespace iMega\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use iMega\Teleport\Parser\Description;
use Gaufrette;

class Parse extends Command
{
    protected function configure()
    {
        $this->setName('parse')
            ->setDescription('Parse xml files')
            ->addArgument('uuid', InputArgument::REQUIRED, 'I need uuid')
            ->addArgument('file', InputArgument::REQUIRED, 'I need file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cliApp = $this->getApplication();
        $app    = $cliApp->getName();

        $app['uuid'] = $input->getArgument('uuid');
        $file        = $input->getArgument('file');

        $res    = $app['storage']->read($file);
        $parser = null;
        if (mb_strpos($res, '</'.Description::CLASSI.'>') > 0) {
            $parser = '\\iMega\\Teleport\\Parser\\Stock';
        } elseif (mb_strpos($res, '</'.Description::PACKAGEOFFERS.'>') > 0) {
            $parser = '\\iMega\\Teleport\\Parser\\Offers';
        } else {
            $output->writeln('Fail. ' . $app['uuid'] . ' and file: ' . $file);
        }

        if (null === $parser) {
            $output->writeln('Fail parser.' . $app['uuid']);
            return;
        }

        $entity = new $parser($res, $app['dispatcher']);
        $entity->parse();

        $output->writeln('Done!'. $app['uuid'] . ' and file: ' . $file);
    }
}
