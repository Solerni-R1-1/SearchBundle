<?php

namespace Orange\SearchBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Orange\SearchBundle\DependencyInjection\Compiler\FilterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * Bundle class.
 */
class OrangeSearchBundle extends PluginBundle  implements ConfigurationProviderInterface
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FilterCompilerPass());
    }
    
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