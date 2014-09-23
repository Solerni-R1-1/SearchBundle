<?php
namespace Orange\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of FilterCompilerPass
 *
 * @author aameziane
 */
class FilterCompilerPass implements CompilerPassInterface
{
    
    public function process(ContainerBuilder $container)
    {
        
        if (!$container->hasDefinition('orange.search.filter_manager')) {
            return;
        }

        $definition = $container->getDefinition(
            'orange.search.filter_manager'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'orange.search.filter'
        );
        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addFilter',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
}
