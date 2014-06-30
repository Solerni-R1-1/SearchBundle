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
    public function indexAction()
    {
        $logger = $this->container->get('logger');
        try {

            $request = $this->getRequest();
            $_format = $request->getRequestFormat();
            $page = $this->getRequest()->get('page') ? $this->getRequest()->get('page') : 1;
            $keywords = $this->getRequest()->get('keywords');
            $filters = $this->getRequest()->get('filters') ? $this->getRequest()->get('filters') : array();
            $itemsPerPage = $this->getRequest()->get('items_per_page') ? $this->getRequest()->get('items_per_page') : 3;
            $client = $this->get('solarium.client');
            // get a select query instance
            $query = $client->createSelect();
            $query->setStart(((int) $page - 1) * $itemsPerPage)->setRows($itemsPerPage);
            $query->setOmitHeader(false);

            if ($request->getMethod() == 'POST') {

                if ($keywords) {
                    $query->setQuery('content:'.$keywords);
                } else {
                    $query->setQuery('*');
                }
            }
            // get highlighting component and apply settings
            $hl = $query->getHighlighting();
            $hl->setFragsize(300);
            $hl->setFields('content');
            $hl->setSimplePrefix('<mark>');
            $hl->setSimplePostfix('</mark>');

            /* Filtrage */
            foreach ($filters as $name => $values) {
                $expression = array();
                foreach ($values as $key => $value) {
                    if ($value == false) {
                        $expression [] = $name.':"'.$key.'"';
                    }
                }
                if ($expression) {
                    $query->createFilterQuery($name)->setQuery("NOT(" . implode(" OR ", $expression) . ")");
                }
            }

            //$query->createFilterQuery('type_name')->setQuery('NOT (type_name:claroline_forum_message OR type_name:claroline_forum_category)');
            //$query->createFilterQuery('owner_name')->setQuery('owner_name:*');

            // get the facetset component
            $facetSet = $query->getFacetSet();
            // create a facet field instance and set options
            $facetSet->createFacetField('type_name')->setField('type_name');
            //$facetSet->createFacetField('owner_id')->setField('owner_id');

            $resultset = $client->select($query);
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
                    'label' => $this->get('translator')->trans($key, array(), 'search')
                );            
                switch ($key) {
                    case 'type_name':
                        foreach ($facet as $value => $count) {
                             $tmp ['value'] []= array(
                                    'count' => $count, 
                                    'value' => $value,
                                    'label' => $this->get('translator')->trans($value, array(), 'search')
                             );
                        }
                        $facets [] = $tmp;
                        break;
                    case 'owner_id':
                        foreach ($facet as $value => $count) {
                        /* @var $owner \Claroline\CoreBundle\Entity\User */
                        $owner = $this->entityManager
                            ->getRepository("ClarolineCoreBundle:User")
                            ->findOneById($value);

                             $tmp ['value'] []= array(
                                    'count' => $count, 
                                    'value' => $value,
                                    'label' => $owner->getFirstName() .' '. $owner->getLastName()
                             );
                        }
                        $facets [] = $tmp;

                    default:
                        break;
                }

            }
        } catch (Exception $ex) {
            $logger->error($ex->getMessage());
        }
        return $this->render(
            'OrangeSearchBundle:Search:response.' . $_format . '.twig', 
                array(
                    'resultset' => $resultset,
                    'documents' => $documents,
                    'facets' => $facets
                )
        );
    }

    

    /**
     * @EXT\Route("/config")
     * 
     */
    public function configAction()
    {
        $solr_endpoints = $this->container->getParameter('index_endpoint');
        //var_dump($solr_endpoints);
    }

    /**
     * @EXT\Route(
     *     "/ping/",
     *     name = "orange_search_ping"
     * )
     *
     * @EXT\Template("OrangeSearchBundle:AdminSolr:index.html.twig")
     *
     * @return Response
     */
    public function pingAction()
    {
        $this->assertIsGranted('ROLE_USER');

        $client = $this->get('solarium.client');
        $ping = $client->createPing();

        try {
            $result = $client->ping($ping);

            return array(
                'status' => 'Serveur accessible'
            );
        } catch (Solarium\Exception $e) {
            return array(
                'status' => 'Serveur non disponible'
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/requeshjgt/page/{page}",
     *     name = "orange_search_request",
     *     defaults={"page"=1}
     * )
     *
     * @EXT\Template("OrangeSearchBundle:Search:reponse.html.twig")
     *
     * @return Response
     */
    public function requestAction($page, $nbByPage = 50)
    {
        $search = $this->getRequest()->get('search');
        $filterType = $this->getRequest()->get('filter_type');
        $this->assertIsGranted('ROLE_USER');

        $client = $this->get('solarium.client');
        $ping = $client->createPing();
        $manager = $this->getDoctrine()->getManager();
        try {
            $result = $client->ping($ping);

            $select = $client->createSelect();
            // get the facetset component
            $facetSet = $select->getFacetSet();

            // create a facet field instance and set options
            $facetSet->createFacetField('content-type')->setField('type_name');
            //$facetSet->createFacetField('wks')->setField('wks_id');

            // Filtrage
            if (($filterType != "all") && ($filterType != "")) {
                $select->createFilterQuery('type_name')->setQuery('type_name:' . $filterType);
            }

            $select->setQuery($search);
            $select->setStart(((int) $page - 1) * $nbByPage)->setRows($nbByPage);
            $select->setOmitHeader(false);

            $request = $client->createRequest($select)->addParam('qt', 'claroline');
            $response = $client->executeRequest($request);
            $results = $client->createResult($select, $response);

            $nb = $results->getNumFound();
            $time = 0;

            // display facet results
            $facetResult = $results->getFacetSet()->getFacet('content-type');
            $facetResultWks = $results->getFacetSet()->getFacet('wks');
            $facetWks = array();
            foreach ($facetResultWks as $key => $frWks) {
                $wks = $manager->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($key);
                $facetWks[$wks->getName()] = $frWks;
            }

            $lr = array();
            foreach ($results as $result) {

                $r = array();
                foreach ($result AS $field => $value) {
                    if (is_array($value))
                        $value = implode(', ', $value);

                    $r[$field] = $value;
                }

                // We check if user has access to the resource
                $resourceNode = $manager->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode')->find($r["resource_id"]);

                if ($this->security->isGranted("open", new ResourceCollection(array($resourceNode)))) {
                    $r["owner"] = true;
                } else {
                    $r["owner"] = false;
                }

                if (!isset($r["mime_type"]))
                    $r["mime_type"] = "";

                // Traitement à faire sur la liste réponse
                // Pour un fichier on tronque le content
                // TODO
                if (($r["mime_type"] == "application/pdf") && isset($r["content"]))
                    $r["content"] = substr($r["content"], 0, 200);

                // Lecture du nom du WKS
                if (isset($r["wks_id"])) {
                    $wks = $manager->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($r["wks_id"]);
                    $r["wks_name"] = $wks->getName();
                } else {
                    $r["wks_name"] = "";
                }

                // TODO - Est-ce qu'il s'agit d'une transcription texte d'une vidéo
                if (isset($r["attr_custom:dailymotion"])) {
                    
                } else
                    $r["attr_custom:dailymotion"] = "";


                // Lecture du sujet du owner
                if (isset($r["user_id"])) {
                    $owner = $manager->getRepository('Claroline\CoreBundle\Entity\User')->find($r["user_id"]);
                    $r["first_name"] = $owner->getFirstName();
                    $r["last_name"] = $owner->getLastName();
                }

                // Lecture du sujet du forum si présent
                if (isset($r["subject_id"])) {
                    $forumSubject = $manager->getRepository('Claroline\ForumBundle\Entity\Subject')->find($r["subject_id"]);
                    $r["name"] = $forumSubject->getTitle();
                }

                array_push($lr, $r);
                unset($r);
            }

            //$pager = $this->pagerFactory->createPagerFromArray($lr, $page, 5);
            //return $this->render('OrangeSearchBundle:Search:reponse.html.twig', array('name' => $name, 'results' => $lr, 'facets' => $facetResult, 'facetsWks' => $facetWks));

            $currentUrl = substr($this->getRequest()->getUri(), 0, strpos($this->getRequest()->getUri(), "/search/request"));

            $ressourceType = array();
            $ressourceType[] = "all_resources";
            $ressourceType[] = "custom/claroline_forum";
            $ressourceType[] = "custom/text";
            $ressourceType[] = "custom/file";
            $ressourceType[] = "custom/claroline_announcement_aggregate";
            $ressourceType[] = "custom/icap_wiki";
            $ressourceType[] = "custom/ujm_exercise";

            return array(
                'name' => $search,
                'nbResults' => $nb,
                'nbByPage' => $nbByPage,
                'page' => $page,
                'results' => $lr,
                'facets' => $facetResult,
                'facetsWks' => $facetWks,
                'url' => $currentUrl,
                'time' => $time,
                'resourcesType' => $ressourceType
            );
        } catch (Solarium\Exception\HttpException $e) {
            echo 'Ping query failed';
        }
    }

    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->security->isGranted($attributes, $object)) {
            throw new AccessDeniedException();
        }
    }

}
