<?php

namespace Orange\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Pager\PagerFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class SearchController extends Controller
{

    private $pagerFactory;
    private $security;
    private $entityManager;
    
    public static $shortCuts = array( 
            'type_name'         => 'type',
            'mooc_category_ids' => 'mcat',
            'mooc_owner_name'   => 'owner',
            'mooc_is_public_b'  => 'ispub'
    );

    /**
     * @DI\InjectParams({
     *     "pagerFactory"  = @DI\Inject("claroline.pager.pager_factory"),
     *     "security"      = @DI\Inject("security.context"),
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "router"        = @DI\Inject("router")
     * })
     */
    public function __construct(
        PagerFactory $pagerFactory, 
        SecurityContextInterface $security,
        EntityManager $entityManager,
        Router $router)
    {
        $this->pagerFactory = $pagerFactory;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->router = $router;

    }
    

    /**
     * @EXT\Route(
     *      "/query.{_format}",
     *      name = "orange_search",
     *      defaults = {"_format" = "html"},
     *      requirements = {"_format" = "json|html"}
     * )
     *
     * @return Response
     */
    public function searchAction($_format)
    {
        if ($_format == 'html') {
            return $this->render('OrangeSearchBundle:Search:response.html.twig');
        }
        $logger = $this->container->get('logger');
        try {
            $client = $this->get('solarium.client');
            $request = $this->getRequest();
            $resultset = $client->select($this->createQuery($request));
            $highlighting = $resultset->getHighlighting();

            $documents = array();
            foreach ($resultset as $document) {
                $doc = array();

                foreach ($document->getFields() as $field => $value) {
                    $doc[$field] = $value;
                }

                if (isset($doc['resource_id'])){
                    $resourceId = $doc['resource_id'];

                    $resourceNode = $this->entityManager
                            ->getRepository("ClarolineCoreBundle:Resource\ResourceNode")
                            ->findOneById($resourceId);

                    $doc['is_granted'] = $this->security->isGranted('OPEN', $resourceNode);
                } else {
                    //todo 
                    $doc['is_granted'] = true;
                }

                $highlightedDoc = $highlighting->getResult($document->id);
                if ($highlightedDoc) {
                    foreach ($highlightedDoc as $field => $highlight) {
                        $doc[$field] =  implode(' (...)', $highlight);
                    }
                }
                /*if ( ! $doc['is_granted'] ) {
                    $doc['content'] = preg_replace('/[\w|&|?]/', 'x', $doc['content']);
                }*/
                $documents [] = $doc;
            }

            $facets = array();
            foreach ($resultset->getFacetSet()->getFacets() as $key => $facet) {
                $tmp = array(
                    'name'  => $key,
                    'label' => $this->get('translator')->trans("facet_".$key, array(), 'search'),
                    'class' => "slrn-facet-$key",
                    'type'  => "checkbox"
                );            
                switch ($key) {
                    case 'ispub':
                        $tmp ['type'] = 'radio';
                    case 'type':
                        foreach ($facet as $value => $count) {
                             $tmp ['value'] []= array(
                                    'count' => $count, 
                                    'value' => $value,
                                    'label' => $this->get('translator')->trans($value, array(), 'search')
                             );
                        }
                        $facets [] = $tmp;
                        break;
                    case 'mcat':

                        foreach ($facet as $value => $count) {
                        /* @var $moocSession \Claroline\CoreBundle\Entity\Mooc\MoocCategory */
                        $moocCategory = $this->entityManager
                            ->getRepository("ClarolineCoreBundle:Mooc\MoocCategory")
                            ->findOneById($value);
                             $tmp ['value'] []= array(
                                    'count' => $count, 
                                    'value' => $value,
                                    'label' => $moocCategory->getName()
                             );
                        }
                        $facets [] = $tmp;                        
                        break;
                    default:
                        foreach ($facet as $value => $count) {
                             $tmp ['value'] []= array(
                                    'count' => $count, 
                                    'value' => $value,
                                    'label' => $value
                             );
                        }
                        $facets [] = $tmp; 
                        break;
                }

            }
            
            ksort($facets);
        } catch (Exception $ex) {
            $logger->error($ex->getMessage());
        }
        return $this->render(
            'OrangeSearchBundle:Search:response.json.twig', 
                array(
                    'resultset' => $resultset,
                    'documents' => $documents,
                    'facets' => $facets
                )
        );
    }
    
    

    /**
     * process request GET query 
     * @param type $request
     * 
     * @return \Solarium\Core\Query 
     */
    private function createQuery($request) {
            
            $keywords = $request->query->get('q');
            $selections = $this->parseQuery($request->query->get('ss'));
            $fixedSelections = $this->parseQuery($request->query->get('fs'));
            $page = $request->query->get('page') ? $request->query->get('page') : 1;
            $itemsPerPage = $request->query->get('rpp') ? $request->query->get('rpp') : 3;
            $ativatedFilters = $request->query->get('afs') ? explode(',', $request->query->get('afs')) : array();
            
            $client = $this->get('solarium.client');
            // get a select query instance
            $query = $client->createSelect();
            $query->setStart(((int) $page - 1) * $itemsPerPage)->setRows($itemsPerPage);
            $query->setOmitHeader(false);
            if ($keywords) {
                $query->setQuery('content:'.$keywords);
            } else {
                $query->setQuery('*');
            }
            // get highlighting component and apply settings
            $hl = $query->getHighlighting();
            $hl->setFragsize(300);
            $hl->setFields('content');
            $hl->setSimplePrefix('<mark>');
            $hl->setSimplePostfix('</mark>');
            
            /* Selection */
            foreach ($selections + $fixedSelections as $shortCut => $values) {
                $name = $this->getNameByShortCut($shortCut);
                $expression = array();
               //if ( isset($values['all']) && $values['all'] === true) {
                    foreach ($values as $key ) {
                            $expression [] = $name.':"'.$key.'"';
                    }
                    if ($expression) {
                        $query->createFilterQuery($name)->setQuery("(" . implode(" OR ", $expression) . ")");
                    }
                //}
            }

            // get the facetset component
            $facetSet = $query->getFacetSet();
            
            // create a facet field instance and set options
            foreach ($ativatedFilters as $activatedFilter) {
                $facetSet->createFacetField($activatedFilter)
                         ->setField($this->getNameByShortCut($activatedFilter));
            }
            return $query;
    }    

    
    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->security->isGranted($attributes, $object)) {
            throw new AccessDeniedException();
        }
    }
    
    private function parseQuery($paramsString)
    {
        $result = array();
        if (!empty($paramsString)) {
            $params = explode(',', $paramsString);
            foreach ($params as $param) {
                $sub_param = explode('__', $param);
                if (count($sub_param) == 2) {
                    $result [$sub_param[0]] [] = $sub_param[1];
                }
            }
        }
        return $result;
    }

    private function getShotCut() 
    {
        return self::$shortCuts;
    }
    
    private function getNameByShortCut($shortCut)
    {
        $result = array_search($shortCut, $this->getShotCut());
        if ($result) {
            return $result;
        } else {
            return $shortCut;
        }
    }

    private function getShortCutByName($name)
    {
        if (isset($this->getShotCut()[$name])) {
            return $this->getShotCut()[$name];
        } else {
            return $name;
        }
    }

}
