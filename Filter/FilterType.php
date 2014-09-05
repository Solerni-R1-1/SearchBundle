<?php

namespace Orange\SearchBundle\Filter;

/**
 * Facet based on the document type
 *
 * @author aameziane
 */
class FilterType extends FilterStandard
{

    public function postProcessResultFacet($resultFacet) 
    {
        
        $returnResultFacet = $this->initResultFacet();
        foreach ($resultFacet as $value => $count) {
            $returnResultFacet ['value'] [] = array(
                'count' => $count,
                'value' => $value,
                'label' => $this->get('translator')->trans($value, array(), 'search')
            );
        }
        return $returnResultFacet;
    }
}
