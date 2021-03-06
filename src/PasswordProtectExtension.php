<?php
// PasswordProtect Extension for Bolt

namespace Bolt\Extension\Bolt\PasswordProtect;

use Bolt\Extension\Bolt\PasswordProtect\Controller\ProtectController;
use Bolt\Extension\Bolt\PasswordProtect\Handler\Checker;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PasswordProtectExtension extends SimpleExtension
{
    public function registerServices(Application $app)
    {

        $app['passwordprotect.handler.checker'] = $app->share(
            function ($app) {
                $config = $app['extensions']->get('Bolt/PasswordProtect')->getConfig();

                return new Checker($app, $config);
            }
        );

        $app['passwordprotect.twig'] = $app->share(
            function ($app) {
                $config = $app['extensions']->get('Bolt/PasswordProtect')->getConfig();

                return new \Bolt\Extension\Bolt\PasswordProtect\Twig\PasswordProtectExtension(
                    $app,
                    $config
                );
            }
        );

        $config = $this->getConfig();

        if (isset($config['contenttype'])) {
            $app->before(function (Request $request) use ($app) {
                return $app['passwordprotect.handler.checker']->checkContentTypeOnRequest($request);
            }, Application::LATE_EVENT);
        }
    }

    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    protected function registerTwigFunctions()
    {
        $app = $this->getContainer();

        return [
            'passwordprotect' => [
                [$app['passwordprotect.twig'],'passwordProtect'],
                ['is_safe' => ['html'], 'is_safe_callback' => true]
            ],
            'passwordform' => [
                [$app['passwordprotect.twig'], 'passwordForm'],
                ['is_safe' => ['html'], 'is_safe_callback' => true]
            ]
        ];
    }

    protected function getDefaultConfig()
    {
        return [
            'encryption' => 'plaintext'
        ];
    }

    protected function registerMenuEntries()
    {
        $changePassword = (new MenuEntry('passwordProtect', '/bolt/protect/changePassword'))
            ->setLabel('PasswordProtect - Set Password')
            ->setIcon('fa:lock');

        $findPasswordHash = (new MenuEntry('generatePasswordHash', '/bolt/protect/generatepasswords'))
            ->setLabel('Generate Password')
            ->setIcon('fa:lock');

        return [
            $changePassword,
            $findPasswordHash
        ];
    }

    protected function registerBackendControllers()
    {
        return [
            '/protect' => new ProtectController($this->getContainer(), $this->getConfig()),
        ];
    }
}
