<?php

namespace iMega\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Gaufrette;

class Parse extends Command
{
    protected function configure()
    {
        $this->setName('parse')
            ->setDescription('Parse xml files')
            ->addArgument('folder', InputArgument::REQUIRED, 'folder to xml files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $folder = $input->getArgument('folder');

        $adapter = new Gaufrette\Adapter\Local($folder);
        $fs = new Gaufrette\Filesystem($adapter);
        $data = $fs->read('import.xml');

        $cliApp = $this->getApplication();

        $stock = new \iMega\Teleport\Parser\Stock($data, $cliApp->getName()['dispatcher']);

        $stock->parse();

        $output->writeln('Done!');
    }
}
