<?php

namespace Orange\SearchBundle\Filter;


/**
 * Description of Filter
 *
 * @author aameziane
 */
class FilterStandard implements InterfaceFilter
{
    
    private $fieldName;
    private $shortCut;
    private $viewType;
    private $resultFacet;
    
    public function __construct($fieldName, $shortCut, $viewType)
    {
        $this->fieldName    = $fieldName;
        $this->shortCut     = $shortCut;
        $this->viewType     = $viewType;
        
        $this->resultFacet  = array(
            'name'  => $this->getShortCut(),
            'label' => $this->getLabel(),
            'class' => $this->getCssClass(),
            'type'  => $this->getViewType()
        );  
    }
    
    public function getQueryExpression($values)
    {
        $expression = array();
        foreach ($values as $key) {
            $expression [] = $this->getFieldName() . ':"' . $key . '"';
        }
        return "(" . implode(" OR ", $expression) . ")";
    }
    
    public function createFacet(&$facetSet)
    {
        $facetSet->createFacetField($this->getShortCut())
                 ->setField($this->getFieldName());
        
    }
    
    public function buildResultFacet($resultFacet) 
    {

        $returnResultFacet = $this->getResultFacet();
        foreach ($resultFacet as $value => $count) {
            $returnResultFacet ['value'] [] = array(
                'count' => $count,
                'value' => $value,
                'label' => $value
            );
        }
        return $returnResultFacet;
    }

    public function getLabel() {
        return $this->get('translator')->trans("facet_".$this->getShortCut(), array(), 'search');
    }
    
    public function getCssClass() {
        return "slrn-facet-".$this->getShortCut();
    }
    
    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getShortCut()
    {
        return $this->shortCut;
    }

    public function getViewType()
    {
        return $this->viewType;
    }

    public function getResultFacet()
    {
        return $this->resultFacet;
    }

    
    public function get($serviceName) 
    {
        
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer()->get($serviceName);
    }


   

}
