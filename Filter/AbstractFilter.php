<?php

namespace Orange\SearchBundle\Filter;


/**
 * Description of Filter
 *
 * @author aameziane
 */
abstract class AbstractFilter implements FilterInterface
{

    public static function getLabel() {
        return self::get('translator')->trans("facet_".static::getShortCut(), array(), 'search');
    }
    
    public static function getCssClass() {
        return "slrn-facet-".static::getShortCut();
    }
    
    public static function initFacetResult() {
        return array(
            'name'  => static::getShortCut(),
            'label' => static::getLabel(),
            'class' => static::getCssClass(),
            'type'  => static::getViewType()
        );  
    }



    public static function getQueryExpression($values)
    {
        $expression = array();
        foreach ($values as $key) {
            $expression [] = static::getName() . ':"' . $key . '"';
        }
        return "(" . implode(" OR ", $expression) . ")";
    }
    
    public static function createFacet(&$facetSet)
    {
        $facetSet->createFacetField(static::getShortCut())
                 ->setField(static::getName());
        
    }
    
    public static function buildResultFacet($resultFacet) {

        $facet = static::initFacetResult();
        
        foreach ($resultFacet as $value => $count) {
            $facet ['value'] [] = array(
                'count' => $count,
                'value' => $value,
                'label' => $value
            );
        }
        return $facet;
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
