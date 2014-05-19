<?php


namespace Orange\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ResultController extends Controller
{
    /**
     * 
     * @param type $document
     */
    public function documentViewAction(
            $document, 
            $debug=false
    )
    {
        $template_name = 
            $this->get('templating')->exists('OrangeSearchBundle:Search:result/'.$document['type_name'].'.html.twig' ) ? 
            'OrangeSearchBundle:Search:result/'.$document['type_name'].'.html.twig' 
            :'OrangeSearchBundle:Search:result/default.html.twig' ;
        
        return $this->render(
            $template_name , 
            array('document'=> $document, 'debug' => $debug)
        );
    }
}
