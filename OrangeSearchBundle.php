<?php

namespace Orange\SearchBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
/**
 * Bundle class.
 */
class OrangeSearchBundle extends PluginBundle  implements ConfigurationProviderInterface
{

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'search');
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        
        if ($bundleClass == 'Nelmio\SolariumBundle\NelmioSolariumBundle') {
            //var_dump($bundleClass);
            $config = new ConfigurationBuilder();
            $config->addContainerResource(__DIR__ . '/Resources/config/indexer/solr.yml');
            
            return $config;
        }
    }

    

}