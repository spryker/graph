<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SprykerEngine\Yves\Application\Communication\Plugin\ServiceProvider;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use SprykerFeature\Shared\Library\Monolog\LumberjackHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;

class MonologServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['logger'] = function () use ($app) {
            return $app['monolog'];
        };

        $app['monolog.logger.class'] = 'Monolog\Logger';

        $app['monolog'] = $app->share(function ($app) {
            $log = new $app['monolog.logger.class']($app['monolog.name']);

            $log->pushHandler($app['monolog.handler']);

            if ($app['debug']) {
                $log->pushHandler($app['monolog.handler.debug']);
            }

            return $log;
        });

        $app['monolog.logfile'] = function () {
            return \SprykerFeature_Shared_Library_Log::getFilePath('message.log');
        };

        $app['monolog.handler.debug'] = function () use ($app) {
            return new StreamHandler($app['monolog.logfile'], $app['monolog.level']);
        };

        $app['monolog.handler'] = function () use ($app) {
            return new LumberjackHandler($app['monolog.level']);
        };

        $app['monolog.level'] = function () {
            return Logger::INFO;
        };

        $app['monolog.name'] = 'yves';
    }

    /**
     * @param Application $app
     * @codeCoverageIgnore
     */
    public function boot(Application $app)
    {
    }
}