<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterType
 *
 * @author aameziane
 */
class FilterType extends FilterStandard
{

    public function buildResultFacet($resultFacet) 
    {
        
        $returnResultFacet = $this->getResultFacet();
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
