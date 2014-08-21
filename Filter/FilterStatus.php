<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterStatus
 *
 * @author aameziane
 */
class FilterStatus extends FilterStandard
{
    
    public function getQueryExpression($values)
    {
        $expression = array();
        foreach ($values as $key) {
            switch ($key) {
                case 'in_progress':
                    $expression [] = '(start_date:[* TO NOW/DAY] AND end_date:[NOW/DAY TO * ])';
                    break;
                case 'coming_soon':
                    $expression [] = 'start_date:[NOW/DAY TO *]';
                    break;
                case 'finished':
                    $expression [] = 'end_date:[* TO NOW/DAY]';
                    break;
                default:
                    break;
            }
        }
        return "(" . implode(" OR ", $expression) . ")";
    }
    
    public function createFacet(&$facetSet)
    {
        $facetSet->createFacetMultiQuery($this->getShortCut())
                             ->createQuery('in_progress', 'start_date:[* TO NOW/DAY] AND end_date:[NOW/DAY TO * ]')
                             ->createQuery('coming_soon', 'start_date:[NOW/DAY TO *]')
                             ->createQuery('finished', 'end_date:[* TO NOW/DAY]');
    }
    
    public function buildResultFacet($resultFacet) {
        $facet = array(
            'name'  => $this->getShortCut(),
            'label' => $this->getLabel(),
            'class' => $this->getClass(),
            'type'  => $this->getViewtype()
        );  
        
        foreach ($resultFacet as $value => $count) {
            $facet ['value'] [] = array(
                'count' => $count,
                'value' => $value,
                'label' => $this->get('translator')->trans($value, array(), 'search')
            );
        }
        return $facet;
    }
}
