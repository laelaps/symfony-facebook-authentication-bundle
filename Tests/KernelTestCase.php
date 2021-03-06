<?php

namespace Laelaps\Bundle\FacebookAuthentication\Tests;

use Absolvent\PHPUnitSymfony\KernelTestCase as BaseKernelTestCase;

class KernelTestCase extends BaseKernelTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getKernelBundles()
    {
        return [
            new \Laelaps\Bundle\FacebookAuthentication\FacebookAuthenticationBundle,
            new \Laelaps\Bundle\FacebookAuthentication\Tests\Fixture\Bundle\Test\TestBundle,
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle,
            new \Symfony\Bundle\SecurityBundle\SecurityBundle,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getKernelConfiguration()
    {
        $config = parent::getKernelConfiguration();

        $config['framework']['router']['resource'] = __DIR__ . '/Resources/config/routing.php';
        $config['framework']['session'] = ['storage_id' => 'session.storage.mock'];
        $config['framework']['templating']['engines'] = ['php'];

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getKernelConfigurationFiles()
    {
        $response = array_merge(
            glob(__DIR__ . '/../Resources/config/*.php'),
            glob(__DIR__ . '/../Resources/config/*.yml'),
            glob(__DIR__ . '/Resources/config/*.php'),
            glob(__DIR__ . '/Resources/config/*.yml')
        );

        $response = array_map('realpath', $response);

        return $response;
    }
}
