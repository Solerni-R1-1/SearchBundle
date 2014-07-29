<?php

namespace Orange\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\IndexableInterface;
use Orange\SearchBundle\Entity\EntityToIndex;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;

class AdministrationController extends Controller
{
    
    
     /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
        EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    
    /**
     * @EXT\Route(
     *      "/admin/entities",
     *      name = "orange_search_admin"
     * )
     *
     * @EXT\Template ("OrangeSearchBundle:Administration:index.html.twig")
     *
     * @Secure(roles="ROLE_ADMIN")
     *  
     * @return Response
     */
    public function indexAction()
    {
        
        $data = array();
        /* var $indexerManager \Orange\SearchBundle\Manager\IndexerManager */
        $indexerManager = $this->get('orange.search.indexer_manager');
        $choices = $indexerManager->getAllIndexableEntities();
        $entityToIndexClassNames = $this->entityManager
                                        ->getRepository('OrangeSearchBundle:EntityToIndex')
                                        ->findEntityToIndexClassNames();
        
        $form = $this->createFormBuilder($data)
                ->add('indexableEntityChoices', 'choice', array(
                    'choices' => $choices,
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'data' => $entityToIndexClassNames))
                ->add('reindexAll', 'checkbox', array(
                    'required' => false, 
                    'data' => false, 
                    'label' => 'form_requeue_all', 
                    'translation_domain' => 'search'))
                ->add('save', 'submit')
                ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $data = $this->getRequest()->get('form');

            $indexableEntityPostChoices = $data['indexableEntityChoices'];

            foreach ($choices as $choice) {
                if ($indexableEntityPostChoices && in_array($choice, $indexableEntityPostChoices)) {
                    $indexerManager->persistEntityToIndex($choice, true);
                } else {
                    $indexerManager->persistEntityToIndex($choice, false);
                }
            }
            $this->getDoctrine()->getManager()->flush();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Vos changements ont été sauvegardés.'
            );
                        
            if (isset($data['reindexAll']) && $data['reindexAll']) {
                $this->get('orange.search.indexer_todo_manager')->deleteAll();
                $this->get('orange.search.indexer_todo_manager')->requeueAll();

                $this->get('session')->getFlashBag()->add(
                    'notice', 'La réindexation a été demandée.'
                );
            }
        }
        return array(
            'form' => $form->createView()
        );
    }

}
