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

class SearchController extends Controller
{
    private $pagerFactory;
    protected $configSolr;
    private $security;

    /**
     * @DI\InjectParams({
     *     "pagerFactory"        = @DI\Inject("claroline.pager.pager_factory"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
    PagerFactory $pagerFactory, SecurityContextInterface $security
    )
    {
        $this->pagerFactory = $pagerFactory;
        $this->security = $security;
    }

    public function indexAction()
    {
        
    }

    /**
     * @EXT\Route("/config")
     * 
     */
    public function configAction()
    {
        $solr_endpoints = $this->container->getParameter('index_endpoint');
        var_dump($solr_endpoints);
    }

    /**
     * @EXT\Route(
     *     "/ping/",
     *     name = "orange_search_ping"
     * )
     *
     * @EXT\Template("OrangeSearchBundle:AdminSolr:ping.html.twig")
     *
     * @return Response
     */
    public function pingAction()
    {
        $this->assertIsGranted('ROLE_USER');

        $configSolr = $this->getSolrConfig();

        $client = new \Solarium\Client($configSolr);
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
     *     "/request/page/{page}",
     *     name = "orange_search_request",
     *     defaults={"page"=1}
     * )
     *
     * @EXT\Template("OrangeSearchBundle:Search:reponse.html.twig")
     *
     * @return Response
     */
    public function requestAction($page, $nbByPage)
    {
        $search = $this->getRequest()->get('search');
        $filterType = $this->getRequest()->get('filter_type');
        $this->assertIsGranted('ROLE_USER');

        $client = new \Solarium\Client($this->getSolrConfig());
        $ping = $client->createPing();
        $nbByPage = 10;

        try {
            $result = $client->ping($ping);

            $select = $client->createSelect();
            // get the facetset component
            $facetSet = $select->getFacetSet();

            // create a facet field instance and set options
            $facetSet->createFacetField('content-type')->setField('type_name');
            $facetSet->createFacetField('wks')->setField('wks_id');

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

    /*
     * get solr host config
     * 
     * @return array config
     */
    private function getSolrConfig()
    {
        return array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $this->container->getParameter('solr.host'),
                    'port' => $this->container->getParameter('solr.port'),
                    'path' => $this->container->getParameter('solr.path')
                )
            )
        );
    }

}
