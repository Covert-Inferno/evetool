<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\AbstractController;
use AppBundle\Controller\ApiControllerInterface;
use AppBundle\Entity\Corporation;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Character controller.
 */
class CharacterController extends AbstractController implements ApiControllerInterface {

    /**
     * @Route("/characters", name="api.characters", options={"expose"=true})
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {

        $user = $this->getUser();

        $characters = $this->getDoctrine()->getRepository('AppBundle:Character')
            ->findBy(['user' => $user ]);

        $json = $this->get('serializer')->serialize($characters, 'json');

        return $this->jsonResponse($json);

    }

    /**
     * Creates a new character entity.
     *
     * @Route("/characters", name="api.character_create.validate", options={"expose"=true})
     * @Secure(roles="ROLE_USER")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $content = $request->request;

        $key = $this->get('app.apikey.manager')
            ->buildInstanceFromRequest($content);

        $validator = $this->get('validator');

        $errors = $validator->validate($key);

        if (count($errors) > 0){
            return $this->getErrorResponse($errors);
        }

        try {
            // new char only has one credential
            $result = $this->get('app.apikey.manager')
                ->validateAndUpdateApiKey($key);

            $arr = $result->toArray();

            $corps = $this->getDoctrine()->getRepository('AppBundle:Corporation');

            foreach ($arr['result']['key']['characters'] as $i => $c){
                $exists = $corps->findOneBy(['eve_id' => $c['corporationID']]);

                $arr['result']['key']['characters'][$i]['best_guess'] = $exists instanceof Corporation;
            }

            return $this->jsonResponse(json_encode($arr));
            /*
            $eveDetails = $result->key->characters[0];
            $char->setName($eveDetails->characterName)
                ->setEveId($eveDetails->characterID);
            */

        } catch (\Exception $e){
            return $this->jsonResponse(json_encode(['message' => $e->getMessage(), 'code' => 400]), 400);
        }

    }

}
