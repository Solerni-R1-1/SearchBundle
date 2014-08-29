<?php

namespace Orange\SearchBundle\SearchFilter;

/**
 * Description of FilterType
 *
 * @author aameziane
 */
class FilterType extends AbstractFilter
{
    
    public static function getName() {
        return 'type_name';
    }
    
    
    public static function getShortCut() {
        return 'type';
    }
    
    
    public static function getViewType() {
        return 'checkbox-all';
    }
    
    public static function buildResultFacet($resultFacet) {
        
        $facet = self::initFacetResult();
        
        foreach ($resultFacet as $value => $count) {
            $facet ['value'] [] = array(
                'count' => $count,
                'value' => $value,
                'label' => self::get('translator')->trans($value, array(), 'search')
            );
        }
        return $facet;
    }
}
