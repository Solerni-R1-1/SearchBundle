<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of FilterInterface
 *
 * @author aameziane
 */
interface InterfaceFilter
{
    public function getFieldName();
    
    public function getShortCut();
    
    public function getViewType();
    
    public function getLabel();
    
    public function getCssClass();
    
    public function getQueryExpression($values);
    
    public function createFacet(&$facetSet);
    
    public function buildResultFacet($resultFacet);
}
