<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\User;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     */
    public function indexAction()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');


        $users = $repo->createQueryBuilder('u')
            ->select('u')->getQuery()->getArrayResult();


        $dataTable = $this->get('app.dataTable.user');
        $dataTable->buildDataTable();

        $dataTable->setData($this->get('jms_serializer')->serialize($users, 'json'));

        return $this->render('AppBundle:Admin/User:index.html.twig', [
            'dataTable' => $dataTable
        ]);
    }
    /**
     * Creates a new User entity.
     *
     * @Route("/", name="user_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
    }


    /**
     * @Route("/new", name="user_new")
     * @Method("GET")
     */
    public function newAction()
    {
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     */
    public function showAction($id)
    {
    }

    /**
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     */
    public function editAction($id)
    {
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
    }
}
