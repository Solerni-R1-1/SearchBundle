<?php

namespace Orange\SearchBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Finder\Finder;
use Orange\SearchBundle\Filter\InterfaceFilter;

/**
 * Description of FilterFactory
 *
 * @author aameziane
 * 
 * @DI\Service("orange.search.filter_manager")
 */

class FilterManager
{
    
    private $filterClassNameMap;
    
    /**
     * @DI\InjectParams({
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        Translator $translator
    )
    {
        $this->translator = $translator;
        $this->filters = array();
    }
    
    public function addFilter(InterfaceFilter $filter, $shortCut) 
    {
        $this->filters[$shortCut] = $filter;
    }

    
    public function getFilter($shortCut) 
    {
        if (array_key_exists($shortCut, $this->filters)) {
           return $this->filters[$shortCut];
        }

        return;
    }
    
    
    public function getFilterClassNameMap()
    {
        return $this->filterClassNameMap;
    }

    public function getFilterClassName($name)
    {

        foreach ($this->getFilterClassNameMap() as $filter) {
            if ($filter['name'] == $name) {
                return $filter['class_name'];
            }
        }
    }
    
    public function getFilterClassNameByShortCut($name)
    {

        foreach ($this->getFilterClassNameMap() as $filter) {
            if ($filter['shortcut'] == $name) {
                return $filter['class_name'];
            }
        }
    }

    public static function get($serviceName)
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer()->get($serviceName);
    }


}
