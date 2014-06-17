<?php

namespace Orange\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\IndexableInterface;
use Orange\SearchBundle\Entity\EntityToIndex;

class AdministrationController extends Controller
{

    /**
     * @EXT\Route(
     *      "/admin/entities",
     *      name = "orange_search_admin"
     * )
     * @EXT\Template ("OrangeSearchBundle:Administration:index.html.twig")
     * 
     * @return Response
     */
    public function indexAction()
    {

        $data = array();
        $indexerManager = $this->get('orange.search.indexer_manager');
        $choices = $indexerManager->getAllResourceIndexableEntities();
        $form = $this->createFormBuilder($data)
                ->add('indexableEntityChoices', 'choice', array(
                    'choices' => $choices,
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'data' => $indexerManager->getEntityToIndexClassNames()))
                ->add('save', 'submit')
                ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $indexableEntityPostChoices = $this->getRequest()->get('form')['indexableEntityChoices'];

            foreach ($choices as $choice) {
                if (in_array($choice, $indexableEntityPostChoices)) {
                    $indexerManager->persistEntityToIndex($choice, true);
                } else {
                    $indexerManager->persistEntityToIndex($choice, false);
                }
            }
            $this->getDoctrine()->getManager()->flush();
            
        }
        return array(
            'form' => $form->createView()
        );
    }

}
