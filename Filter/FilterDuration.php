<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterStatus
 *
 * @author aameziane
 */
class FilterDuration extends AbstractFilter
{
    
    public static function getName() {
        return 'duration';
    }
    
    
    public static function getShortCut() {
        return 'duration';
    }
    
    
    public static function getViewType() {
        return 'checkbox-all';
    }


    public static function getQueryExpression($values)
    {
        $expression = array();
        foreach ($values as $key) {
            switch ($key) {
                case 'less_4':
                    $expression [] = 'mooc_duration_i:[* TO 3]';
                    break;
                case 'between_4_6':
                    $expression [] = 'mooc_duration_i:[4 TO 6]';
                    break;
                case 'more_6':
                    $expression [] = 'mooc_duration_i:[7 TO *]';
                    break;
                default:
                    break;
            }
        }
        return "(" . implode(" OR ", $expression) . ")";
    }
    
    public static function createFacet(&$facetSet)
    {
        $facetSet->createFacetMultiQuery(static::getShortCut())
                             ->createQuery('less_4', 'mooc_duration_i:[* TO 3]')
                             ->createQuery('between_4_6', 'mooc_duration_i:[4 TO 6]')
                             ->createQuery('more_6', 'mooc_duration_i:[7 TO *]');
    }
    
    public static function buildResultFacet($resultFacet) {
        $facet = $facet = static::initFacetResult();
        
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
