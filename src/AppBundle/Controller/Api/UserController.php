<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\AbstractController;
use AppBundle\Controller\ApiControllerInterface;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * User controller.
 *
 * @Route("/user", options={"expose"=true})
 */
class UserController extends AbstractController implements ApiControllerInterface {

    /**
     * Lists all User entities.
     *
     * @Route("/", name="api.users")
     * @Method("GET")
     */
    public function indexAction()
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')
            ->findAll();

        $json = $this->get('jms_serializer')->serialize($users, 'json');

        return $this->jsonResponse($json);

    }
    /**
     * Creates a new User entity.
     *
     * @Route("/", name="api.user_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $content = $request->request;

        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();

        $user->setUsername($content->get('username'))
            ->setEmail($content->get('email'));

        $validator = $this->get('validator');

        $errors = $validator->validate($user);

        var_dump($errors);die;
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}", name="api.user_show")
     * @Method("GET")
     */
    public function showAction($id)
    {
    }

    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="api.user_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="api.user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
    }
}
