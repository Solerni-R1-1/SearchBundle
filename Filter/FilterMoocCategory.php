<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterType
 *
 * @author aameziane
 */
class FilterMoocCategory extends FilterStandard
{

    public function buildResultFacet($resultFacet) {
        $facet = array(
            'name'  => $this->getShortCut(),
            'label' => $this->getLabel(),
            'class' => $this->getClass(),
            'type'  => $this->getViewtype()
        );  
        
        foreach ($resultFacet as $value => $count) {
        /* @var $moocSession \Claroline\CoreBundle\Entity\Mooc\MoocCategory */
            $moocCategory = $this->get('doctrine')
                ->getEntityManager()
                ->getRepository("ClarolineCoreBundle:Mooc\MoocCategory")
                ->findOneById($value);

            if ($moocCategory) {
                $facet ['value'] []= array(
                       'count' => $count, 
                       'value' => $value,
                       'label' => $moocCategory->getName()
                );
            }
        }
        return $facet;
    }
}
