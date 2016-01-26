<?php

namespace iMega\Oslo;

use iMega\Teleport\MainController;

use iMega\Teleport\Subscriber\BufferSubscriber;
use iMega\Teleport\Subscriber\PackerSubscriber;
use Silex\Application;

require_once __DIR__.'/../../vendor/autoload.php';

$options = require_once __DIR__ . '/../../config/options.php';
$appConfig = require_once __DIR__ . '/../../config/app.php';
$app = new Application(array_merge_recursive($appConfig, $options));

$app['debug'] = true;
$app['dispatcher']->addSubscriber(new BufferSubscriber($app['buffer']));
$app['dispatcher']->addSubscriber(new PackerSubscriber($app['buffer'], $app['mapper'], 9999999));

$app->run();

$main = new MainController();
$main->import($app, new \Symfony\Component\HttpFoundation\Request());
