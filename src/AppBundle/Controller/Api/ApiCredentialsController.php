<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\AbstractController;
use AppBundle\Controller\ApiControllerInterface;
use AppBundle\Entity\ApiCredentials;
use AppBundle\Entity\Corporation;
use Doctrine\DBAL\DBALException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * ApiCredentials Controller controller.
 */
class ApiCredentialsController extends AbstractController implements ApiControllerInterface {

    /**
     * @Route("/corporation/{id}/api_credentials", name="api.corporation.apicredentials", options={"expose"=true})
     * @ParamConverter(name="corp", class="AppBundle:Corporation")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function indexAction(Request $request, Corporation $corp)
    {

        $credentials = $this->getDoctrine()->getManager()
            ->getRepository('AppBundle:ApiCredentials')
            ->findBy(['corporation' => $corp]);

        $json = $this->get('serializer')->serialize($credentials, 'json');

        return $this->jsonResponse($json);

    }

    /**
     * @Route("/corporation/{id}/api_credentials", name="api.corporation.apicredentials.post", options={"expose"=true})
     * @ParamConverter(name="corporation", class="AppBundle:Corporation")
     * @Secure(roles="ROLE_ADMIN")
     * @Method("POST")
     */
    public function newAction(Request $request,  Corporation $corporation){

        $content = $request->request;

        $newKey = $this->get('app.apikey.manager')
            ->buildInstanceFromRequest($content);

        $validator = $this->get('validator');

        $errors = $validator->validate($newKey);

        if (count($errors) > 0){
            return $this->getErrorResponse($errors);
        }

        $corporation->addApiCredential($newKey);

        $em = $this->getDoctrine()->getManager();

        $em->persist($newKey);
        try {
            $em->flush();
        } catch (DBALException $e) {
            return $this->jsonResponse(json_encode(['message' => $e->getMessage()]), 409);
        }

        return $this->jsonResponse($this->get('serializer')->serialize($newKey, 'json'));


    }

    /**
     * @Route("/corporation/{id}/api_credentials", name="api.corporation.apicredentials.update", options={"expose"=true})
     * @ParamConverter(name="credentials", class="AppBundle:ApiCredentials")
     * @Secure(roles="ROLE_ADMIN")
     * @Method("PATCH")
     */
    public function updateAction(Request $request, ApiCredentials $credentials)
    {

        //@TODO clean this up please
        $em = $this->getDoctrine()->getManager();

        if ($request->query->get('delete', false) && $credentials->getIsActive()){
            $credentials->setIsActive(false);

            $em->persist($credentials);
            $em->flush();
        }

        if ($request->query->get('enable', false) && !$credentials->getIsActive()){

            $credentials->setIsActive(true);

            $em->persist($credentials);
            $em->flush();
        }

        $json = $this->get('serializer')->serialize($credentials, 'json');

        return $this->jsonResponse($json);

    }
}
