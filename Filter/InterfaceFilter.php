<?php

namespace Orange\SearchBundle\Filter;

/**
 * FilterInterface  
 *
 * @author aameziane
 */
interface InterfaceFilter
{
    /**
     * Get solr concerned filed name
     * 
     * @return string
     */
    public function getFieldName();
    
    /**
     * Get filter shortCut, used in the url request
     * 
     * @return string
     */
    public function getShortCut();
    
    /**
     * How the front end should be display the filter (checkbox)
     * 
     * @return string
     */
    public function getViewType();
    
    
    /**
     * get label of the filter 
     * 
     * @return string
     */
    public function getLabel();
    
    /**
     * get generated css class of the filter
     * 
     * @return string
     */
    public function getCssClass();
    
    /**
     * Get solr Query Expression 
     * 
     * @param array $values filters values
     * 
     * @return string
     */
    public function getQueryExpression($values);
    
    
    /**
     * Create solaruim facet query 
     * 
     * @param Facetset $facetSet the facetset component
     */
    public function createFacet(&$facetSet);
    
    
    /**
     * Post processing solr result facet 
     * 
     * @param array $resultFacet solr facet result
     * 
     * @return array processed result Facet
     */
    public function postProcessResultFacet($resultFacet);
}
