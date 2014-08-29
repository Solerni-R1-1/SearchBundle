<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterType
 *
 * @author aameziane
 */
class FilterMoocCategory extends AbstractFilter
{

    public static function getName() {
        return 'mooc_category_ids';
    }
    
    
    public static function getShortCut() {
        return 'mcat';
    }
    
    
    public static function getViewType() {
        return 'checkbox-all';
    }
    
    public static function buildResultFacet($resultFacet) {
        
        $facet = static::initFacetResult();
        
        foreach ($resultFacet as $value => $count) {
        /* @var $moocSession \Claroline\CoreBundle\Entity\Mooc\MoocCategory */
            $moocCategory = self::get('doctrine')
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
