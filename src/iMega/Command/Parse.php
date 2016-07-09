<?php

namespace iMega\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Gaufrette;
use iMega\Teleport\Parser;
use iMega\Teleport\StorageInterface;

class Parse extends Command
{
    protected function configure()
    {
        $this->setName('parse')
            ->setDescription('Parse xml files')
            ->addArgument('uuid', InputArgument::REQUIRED, 'I need uuid');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cliApp = $this->getApplication();
        $app    = $cliApp->getName();
        $app['uuid'] = $input->getArgument('uuid');
        /**
         * @var StorageInterface $storage
         */
        $storage = $app['storage'];

        $keyStock = $this->detectFileByType(
            $storage,
            Parser\Description::CLASSI
        );
        if (!empty($keyStock)) {
            $stock = new Parser\Stock(
                $storage->read($keyStock),
                $app['dispatcher']
            );
            $stock->parse();
        }

        $keyOffer = $this->detectFileByType(
            $storage,
            Parser\Description::PACKAGEOFFERS
        );
        if (!empty($keyOffer)) {
            $offers = new Parser\Offers(
                $storage->read($keyOffer),
                $app['dispatcher']
            );
            $offers->parse();
        }

        $output->writeln('Done!');
    }

    /**
     * @param $storage
     * @param $type
     *
     * @return mixed
     */
    private function detectFileByType($storage, $type)
    {
        /**
         * @var \Gaufrette\Filesystem $storage
         */
        foreach ($storage->keys() as $file) {
            if (strpos($file, '.xml') >= 1) {
                $res = $storage->read($file);
                if (mb_strpos($res, $type) > 0) {
                    return $file;
                }
            }
        }
    }
}
