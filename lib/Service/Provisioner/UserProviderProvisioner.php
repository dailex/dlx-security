<?php

namespace Dlx\Security\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Service\Provisioner\ProvisionerInterface;
use Dailex\Service\ServiceDefinitionInterface;
use Dlx\Security\Authentication\UserTokenBasedRememberMeServices;
use Dlx\Security\EventListener\OauthInfoListener;
use Dlx\Security\EventListener\UserLocaleListener;
use Dlx\Security\EventListener\UserLoginListener;
use Dlx\Security\EventListener\UserLogoutListener;
use Gigablah\Silex\OAuth\OAuthServiceProvider;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderProvisioner implements ProvisionerInterface, EventListenerProviderInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $crateSettings = $configProvider->get('crates.dlx.security', []);

        // allow override of routing prefix from crate settings
        $routingPrefix = $crateSettings['mount'];
        if ($routingPrefix === '/') {
            $routingPrefix = '';
        }

        // Define the user service and delegate upfront for the security provider
        $injector->share($serviceClass)->alias(UserProviderInterface::class, $serviceClass);
        $userProviderDelegate = function () use ($injector, $serviceClass) {
            return $injector->make($serviceClass);
        };

        // setup firewalls
        $devFirewall = $app['debug'] ? [
            'development' => [
                'pattern' => '^/_(profiler|wdt)/',
                'security' => false
           ]
       ] : [];

        $securityFirewalls = array_replace_recursive(
            $devFirewall,
            [
                // @todo better default firewall config
                'default' => [
                    'pattern' => "^.*$",
                    'anonymous' => true,
                    'users' => $userProviderDelegate
               ]
            ],
            $crateSettings['firewalls'] ?? []
        );

        // register oauth services
        if ($oauthSettings = $crateSettings['oauth'] ?? []) {
            $oauthServices = [];
            if ($facebookSettings = $oauthSettings['facebook']) {
                if ($facebookSettings['enabled']) {
                    $oauthServices['Facebook'] = [
                        'key' => (string) $facebookSettings['app_key'],
                        'secret' => (string) $facebookSettings['app_secret'],
                        'scope' => (array) $facebookSettings['scope'],
                        'user_endpoint' => sprintf(
                            'https://graph.facebook.com/me?fields=%s',
                            implode(',', (array) $facebookSettings['fields'] ?? ['id', 'name', 'email'])
                        )
                   ];
                }
            }

            if (!empty($oauthServices)) {
                // merge oauth firewalls
                $securityFirewalls = array_merge(
                    [
                        'oauth' => [
                            // provide security context to specific firewall
                            'context' => $oauthSettings['context'] ?? 'default',
                            'pattern' => "^$routingPrefix/oauth/",
                            'anonymous' => true,
                            'oauth' => [
                                'login_path' => "$routingPrefix/oauth/{service}",
                                'callback_path' => "$routingPrefix/oauth/{service}/callback",
                                'check_path' => "$routingPrefix/oauth/{service}/check",
                                // @todo check the following are properly generated paths
                                'failure_path' => 'hlx.security.login',
                                'default_target_path' => 'home',
                                'with_csrf' => true
                           ],
                            'users' => $userProviderDelegate
                       ]
                   ],
                    $securityFirewalls
                );

                $app->register(
                    new OAuthServiceProvider,
                    [
                        'oauth.services' => $oauthServices,
                        'oauth.user_info_listener' => function ($app) use ($oauthSettings) {
                            return new OauthInfoListener($app['oauth'], $app['oauth.services'], $oauthSettings);
                        }
                   ]
                );
            }
        }

        // setup roles and rules
        $accessRules = [];
        $roleHierarchy =  [
            'ROLE_ADMIN' => ['ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH'],
            'administrator' => ['ROLE_ADMIN'],
            'user' => ['ROLE_USER']
       ];

        if ($rolesSettings = $crateSettings['roles'] ?? []) {
            $roleHierarchy = array_merge(
                $roleHierarchy,
                $rolesSettings['role_hierarchy'] ?? []
            );
            $accessRules = array_merge(
                $rolesSettings['access_rules'] ?? [],
                $accessRules
            );
        }

        // register the security service
        $app->register(
            new SecurityServiceProvider,
            [
                'security.firewalls' => $securityFirewalls,
                'security.access_rules' => $accessRules,
                'security.role_hierarchy' => $roleHierarchy
           ]
        );

        // register after SecurityServiceProvider
        $app->register(new RememberMeServiceProvider);

        $this->registerRememberMeServices($app);
        $this->registerAuthenticators($app, $injector, $crateSettings['authenticators'] ?? []);
        $this->registerSecurityVoters($app, $injector, $crateSettings['voters'] ?? []);
        $this->registerLogoutHandler($app, $injector);
        $this->registerLoginHandler($app, $injector);
    }


    private function registerLogoutHandler(Container $app, Injector $injector)
    {
        // logout handler - 'default' matching firewall name
        $app['security.authentication.logout_handler.default'] = function ($app) use ($injector) {
            return $injector->share(UserLogoutListener::class)->make(
                UserLogoutListener::CLASS,
                [':targetUrl' => $app['security.firewalls']['default']['logout']['target_url']]
            );
        };
    }

    private function registerLoginHandler(Container $app, Injector $injector)
    {
        // 'default' matching firewall name
        $app['security.authentication.success_handler.default'] = function ($app) use ($injector) {
            return $injector->share(UserLoginListener::class)->make(
                UserLoginListener::class,
                [':options' => $app['security.firewalls']['default']['form']]
            );
        };
    }

    private function registerSecurityVoters(Container $app, Injector $injector, array $voterSettings = [])
    {
        $app['security.voters'] = $app->extend('security.voters', function ($voters) use ($injector, $voterSettings) {
            foreach ($voterSettings as $voter) {
                $voters[] = $injector->make($voter);
            }
            return $voters;
        });
    }

    private function registerAuthenticators(Container $app, Injector $injector, array $authenticatorSettings = [])
    {
        foreach ($authenticatorSettings as $name => $authenticator) {
            $app[$name] = function () use ($injector, $authenticator) {
                return $injector->make($authenticator);
            };
        }
    }

    /*
     * Overriding default to support Dailex user token validation within cookie auto login flow
     */
    private function registerRememberMeServices(Container $app)
    {
        $app['security.remember_me.service._proto'] = $app->protect(function ($providerKey, $options) use ($app) {
            return function () use ($providerKey, $options, $app) {
                $options = array_replace([
                    'name' => 'REMEMBERME',
                    'lifetime' => 31536000,
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => true,
                    'always_remember_me' => false,
                    'remember_me_parameter' => '_remember_me',
               ], $options);

                return new UserTokenBasedRememberMeServices(
                    [$app['security.user_provider.'.$providerKey]],
                    $options['key'],
                    $providerKey,
                    $options,
                    $app['logger']
                );
            };
        });
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        if (isset($app['session'])) {
            $dispatcher->addSubscriber(new UserLocaleListener($app['session']));
        }
    }
}
