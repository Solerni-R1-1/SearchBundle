<?php

namespace Orange\SearchBundle\Filter;

use Orange\SearchBundle\Filter\FilterStandard;
use Orange\SearchBundle\Filter\FilterStatus;

/**
 * Description of FilterFactory
 *
 * @author aameziane
 */
class FilterFactory
{
    public static $shortCuts = array( 
            'type_name'         => 'type',
            'mooc_category_ids' => 'mcat',
            'mooc_owner_name'   => 'owner',
            'mooc_is_public_b'  => 'ispub'
    );
    
    public static function create($shortCut)
    {
        $filter = null;
        
        $name  = self::getNameByShortCut($shortCut);
        $label = self::get('translator')->trans("facet_".$shortCut, array(), 'search');
        $class = "slrn-facet-$shortCut";
        
        switch ($shortCut) {
            case 'status':
                $filter = new FilterStatus($name, $shortCut, "checkbox-all", $label, $class);
                break;
            
            case 'mcat':
                $filter = new FilterMoocCategory($name, $shortCut, "checkbox-all", $label, $class);
                break;
            
            case 'type':
                $filter = new FilterType($name, $shortCut, "checkbox-all", $label, $class);
                break;
            
            case 'owner':
                $filter = new FilterStandard($name, $shortCut, "checkbox-all", $label, $class);
                break;
            
            case 'duration':
                $filter = new FilterDuration($name, $shortCut, "checkbox-all", $label, $class);
                break;
            default:
                $filter = new FilterStandard($name, $shortCut, "checkbox", $label, $class);
                break;
        }
        
        return $filter;
    }
    
    private static function getShotCut() 
    {
        return self::$shortCuts;
    }
    
    public static function getNameByShortCut($shortCut)
    {
        $result = array_search($shortCut,  self::getShotCut());
        if ($result) {
            return $result;
        } else {
            return $shortCut;
        }
    }

    public static function getShortCutByName($name)
    {
        if (isset( self::getShotCut()[$name])) {
            return self::getShotCut()[$name];
        } else {
            return $name;
        }
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
