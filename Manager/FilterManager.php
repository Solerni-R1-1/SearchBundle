<?php

namespace Orange\SearchBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
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
    
    private $filters;
    
    public function __construct()
    {
        $this->filters = array();
    }
    
    public function addFilter(InterfaceFilter $filter, $alias) 
    {
        $this->filters[$alias] = $filter;
    }

    
    public function getFilter($shortCut) 
    {
        if (array_key_exists($shortCut, $this->filters)) {
           return $this->filters[$shortCut];
        }

        return;
    }
}
