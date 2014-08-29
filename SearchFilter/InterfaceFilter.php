<?php

namespace Orange\SearchBundle\SearchFilter;

/**
 * Description of FilterInterface
 *
 * @author aameziane
 */
interface InterfaceFilter
{
    public static function getName();
    
    public static function getShortCut();
    
    public static function getViewType();
    
    public static function getLabel();
    
    public static function getCssClass();
    
    public static function getQueryExpression($values);
    
    public static function createFacet(&$facetSet);
    
    public static function buildResultFacet($resultFacet);
}
