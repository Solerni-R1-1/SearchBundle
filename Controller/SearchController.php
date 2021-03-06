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
use Orange\SearchBundle\Filter\FilterFactory;

class SearchController extends Controller
{

    private $pagerFactory;
    private $security;
    private $entityManager;

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
            $query = $request->query->get("q");
            if (substr_count($query, "\"") % 2 != 0) {
            	$request->query->set("q", $query."\"");
            }
            $resultset = $client->select($this->createQuery($request));
            $highlighting = $resultset->getHighlighting();

            $documents = array();
            foreach ($resultset as $document) {
                $doc = array();

                foreach ($document->getFields() as $field => $value) {
                    $doc[$field] = $value;
                }

                $highlightedDoc = $highlighting->getResult($document->id);
                if ($highlightedDoc) {
                    foreach ($highlightedDoc as $field => $highlight) {
                        $doc[$field] =  implode(' (...)', $highlight);
                    }
                }

                $documents [] = $doc;
            }
            
            //rebuild facetes from result
            $facets = array();
            foreach ($resultset->getFacetSet()->getFacets() as $name => $facet) {
                
                $filter = $this->get('orange.search.filter_manager')
                               ->getFilter($name);
                $facets [] = $filter->postProcessResultFacet($facet);
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
        
        $logger = $this->container->get('logger');

        // get query string (keywords)
        $keywords = $request->query->get('q');
        // get filters
        $selections = $this->parseQuery($request->query->get('ss'));
        // get fixed filters
        $fixedSelections = $this->parseQuery($request->query->get('fs'));
        // get page
        $page = $request->query->get('page') ? $request->query->get('page') : 1;
        // get item per page
        $itemsPerPage = $request->query->get('rpp') ? $request->query->get('rpp') : 3;
        // get activated filters
        $ativatedFilters = $request->query->get('afs') ? explode(',', $request->query->get('afs')) : array();

        $client = $this->get('solarium.client');
        // get a select query instance
        $query = $client->createSelect();
        $query->setStart(((int) $page - 1) * $itemsPerPage)->setRows($itemsPerPage);
        $query->setOmitHeader(false);
        if ($keywords) {
            $keywords = explode(" ", $keywords);
            $queryString = "(";
            foreach ($keywords as $i => $keyword) {
                $queryString = $queryString.($i == 0 ? "" : " OR ")."\"".$keyword."\"";
            }
            $queryString = $queryString.")";
            $query->setQuery('content_t:'.$queryString);
        } else {
            $query->setQuery('*');
        }

        // Add sort order for mooc session
        if ($fixedSelections) {
            if (in_array( array('claroline_core_mooc_moocsession'), $fixedSelections)) {
                $query->addSort('start_date', 'DESC');
            }
        } else {
            if (!$selections || !array_key_exists('type', $selections) || in_array('claroline_core_mooc_moocsession', $selections['type'])) {
                $query->addSort('exists(start_date)', 'DESC');
                $query->addSort('score', 'DESC');
            }
        }

        // get highlighting component and apply settings
        $hl = $query->getHighlighting();
        $hl->setFragsize(85);
        $hl->setFields('content_t, mooc_description_t, mooc_about_description_t');
        $hl->setSimplePrefix('<mark>');
        $hl->setSimplePostfix('</mark>');
        // get the facetset component
        $facetSet = $query->getFacetSet();

        //access role filters
        $accessRoleExpressionArray = array();
        foreach ($this->getUserRolesIds() as $id) {
           $accessRoleExpressionArray [] = 'access_role_ids:"' . $id . '"';
        }
        $accessRoleExpression = "(" . implode(" OR ", $accessRoleExpressionArray) . ")";
        $logger->info($accessRoleExpression);
        $query->createFilterQuery('access_role_ids')->setQuery($accessRoleExpression);

        /* Selection */
        foreach ($selections + $fixedSelections as $shortCut => $values) {

            $filter = $this->get('orange.search.filter_manager')
                           ->getFilter($shortCut);
            if ($filter) {
                $expression = $filter->getQueryExpression($values);

                if ($expression) {
                    $logger->info($expression);
                    $query->createFilterQuery($filter->getFieldName())->setQuery($expression);
                }
            }
        }

        // create a facet field instance and set options
        foreach ($ativatedFilters as $activatedFilter) {
            $filter = $this->get('orange.search.filter_manager')
                           ->getFilter($activatedFilter);

            $filter->createFacet($facetSet);

        }
        return $query;
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
    
    
    /**
     * get current user roles ids
     * 
     * @return array user roles id
     */
    private function getUserRolesIds()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $roleManager = $this->get('claroline.manager.role_manager');
        
        $userAccessRoleIds = array();

        if ( $user === 'anon.') {
            $userAccessRoleIds [] = $roleManager
                                         ->getRoleByName('ROLE_ANONYMOUS')
                                         ->getId();
        } else {
            $userAccessRoleIds = array_map(function ($role) { return (int) $role->getId(); }, $user->getEntityRoles()->toArray());
            // Add Collaborator roles for user with constraints
            if (  $user->getSessionsByUsers() ) {
                foreach( $user->getSessionsByUsers() as $constraint ) {
                    $userAccessRoleIds[] = $roleManager->getCollaboratorRole( $constraint->getMoocSession()->getMooc()->getWorkspace() )->getId();       
                }
            }
        }

        return $userAccessRoleIds;
    } 
    
}
