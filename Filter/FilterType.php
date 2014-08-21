<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterType
 *
 * @author aameziane
 */
class FilterType extends FilterStandard
{

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
