<?php

use Silex\Application;

return [
    'storage.adapter' => function (Application $app) {
        return new Gaufrette\Adapter\Local('/data/' . $app['uuid'], true);
    },
    'storage.temp.adapter' => function (Application $app) {
        return new Gaufrette\Adapter\Local('/tmp/' . $app['uuid'], true);
    },
    'storage' => function (Application $app) {
        $fs = new Gaufrette\Filesystem($app['storage.adapter']);

        $tmpFs = new Gaufrette\Filesystem($app['storage.temp.adapter']);
        $filesystemMap = Gaufrette\StreamWrapper::getFilesystemMap();
        $filesystemMap->set('teleport', $tmpFs);
        Gaufrette\StreamWrapper::register();

        return $fs;
    },
    'resources' => function () {
        $adapter = new Gaufrette\Adapter\Local(__DIR__ . '/../resources');
        return new Gaufrette\Filesystem($adapter);
    },
    'buffer' => function () {
        return new \iMega\Teleport\Buffers\Memory();
    },
    'mapper' => function (Application $app) {
        return new iMega\Mapper\SqlDump($app['db.options']);
    }
];
