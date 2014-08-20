<?php

namespace Orange\SearchBundle\Filter;

/**
 * Description of Filter
 *
 * @author aameziane
 */
class FilterStandard
{
    private $name;
    private $shortCut;
    private $viewtype;
    private $label;
    private $class;
    
    function __construct($name, $shortCut, $viewtype, $label, $class)
    {
        $this->name     = $name;
        $this->shortCut = $shortCut;
        $this->viewtype = $viewtype;
        $this->label    = $label;
        $this->class    = $class;
    }

    
    public function getQueryExpression($values)
    {
        $expression = array();
        foreach ($values as $key) {
            $expression [] = $this->getName() . ':"' . $key . '"';
        }
        return "(" . implode(" OR ", $expression) . ")";
    }
    
    public function createFacet(&$facetSet)
    {
        $facetSet->createFacetField($this->shortCut)
                 ->setField($this->name);
        
    }
    
    public function buildResultFacet($resultFacet) {
        $facet = array(
            'name'  => $this->getShortCut(),
            'label' => $this->getLabel(),
            'class' => $this->getClass(),
            'type'  => $this->getViewtype()
        );  
        
        foreach ($resultFacet as $value => $count) {
            $facet ['value'] [] = array(
                'count' => $count,
                'value' => $value,
                'label' => $value
            );
        }
        return $facet;
    }
    
    
    public function getName()
    {
        return $this->name;
    }

    public function getShortCut()
    {
        return $this->shortCut;
    }

    public function getViewtype()
    {
        return $this->viewtype;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setShortCut($shortCut)
    {
        $this->shortCut = $shortCut;
    }

    public function setViewtype($viewtype)
    {
        $this->viewtype = $viewtype;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setClass($class)
    {
        $this->class = $class;
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
