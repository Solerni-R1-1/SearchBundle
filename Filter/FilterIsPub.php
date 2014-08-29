<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterType
 *
 * @author aameziane
 */
class FilterIsPub extends AbstractFilter
{

    public static function getName() {
        return 'mooc_is_public_b';
    }
    
    
    public static function getShortCut() {
        return 'ispub';
    }
    
    
    public static function getViewType() {
        return 'checkbox';
    }
    
}
