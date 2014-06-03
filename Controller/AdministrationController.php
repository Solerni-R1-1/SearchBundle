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
        $choices = $this->getAllResourceIndexableEntities();
        $form = $this->createFormBuilder($data)
                ->add('indexableEntityChoices', 'choice', array(
                    'choices' => $choices,
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'data' => $this->getEntityToIndexClassNames()))
                ->add('save', 'submit')
                ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $indexableEntityPostChoices = $this->getRequest()->get('form')['indexableEntityChoices'];

            foreach ($choices as $choice) {
                if (in_array($choice, $indexableEntityPostChoices)) {
                    $this->persistEntityToIndex($choice, true);
                } else {
                    $this->persistEntityToIndex($choice, false);
                }
            }
            $this->getDoctrine()->getManager()->flush();
            
        }
        return array(
            'form' => $form->createView()
        );
    }

    
    private function persistEntityToIndex($className, $isToIndex)
    {
        $em = $this->getDoctrine()->getManager();
        $entityToIndex = $em->getRepository('OrangeSearchBundle:EntityToIndex')->findOneByClassName($className);
        if (!$entityToIndex) {
            $entityToIndex = new EntityToIndex();
        } 
        $entityToIndex->setClassName($className);
        $entityToIndex->setToIndex($isToIndex);
        
        $em->persist($entityToIndex);
    }
    

    private function getAllResourceIndexableEntities()
    {
        $em = $this->getDoctrine()->getManager();
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        //all entities
        $entityNames = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $choices = array();

        foreach ($resourceTypes as $resourceType) {
            if ($resourceType->getPlugin()) {
                //get bundle root
                $bundleFQCN = explode("\\", $resourceType->getPlugin()->getBundleFQCN());
                array_pop($bundleFQCN);
                $bundleRoot = implode("\\", $bundleFQCN);

                foreach ($entityNames as $entityName) {
                    if (strrpos($entityName, $bundleRoot) !== false &&
                            array_key_exists('Claroline\CoreBundle\Entity\IndexableInterface', class_implements($entityName))) {
                        $choices [$entityName] = $entityName;
                    }
                }
            }
        }
        return $choices;
    }

    
    private function getEntityToIndexClassNames()
    {
        $em = $this->getDoctrine()->getManager();
        $classNames = array();
        $entitiesToIndex = $em->getRepository('OrangeSearchBundle:EntityToIndex')->findByToIndex(true);
        foreach ($entitiesToIndex as $entity) {
            $classNames [] = $entity->getClassName();
        }
        return $classNames;
    }
}
