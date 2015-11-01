<?php

namespace AppBundle\Service\Manager;

use AppBundle\Entity\ApiCredentials;
use AppBundle\Entity\Corporation;
use AppBundle\Exception\InvalidAccessMaskException;
use AppBundle\Exception\InvalidApiKeyTypeException;
use AppBundle\Exception\InvalidExpirationException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tarioch\PhealBundle\DependencyInjection\PhealFactory;

class ApiKeyManager implements DataManagerInterface {

    private $pheal;

    private $doctrine;

    public function __construct(PhealFactory $pheal, Registry $doctrine) {
        $this->pheal = $pheal;
        $this->doctrine = $doctrine;
    }

    public function validateAndUpdateApiKey(ApiCredentials $entity, $required_type = false) {
        $client = $this->getClient($entity, 'account');

        $result = $client->APIKeyInfo();

        $key = $result->key;

        list($type, $expires, $accessMask) = [$key->type, $key->expires, $key->accessMask];

        if (strlen($expires) > 0) {
            throw new InvalidExpirationException('Expiration Date on API Key is finite.');
        }

        if ($accessMask !== '1073741823'){
            throw new InvalidAccessMaskException('Your Access Mask is invalid - please use the link above and CHECK No Expiry to generate a valid key');
        }

        if ($required_type && $type !== $required_type){
            throw new InvalidApiKeyTypeException('Api Key must be of type:'.$required_type.' - '.$type.' given');
        }

        $exists = $this->doctrine->getRepository('AppBundle:ApiCredentials')
            ->findOneBy(['api_key' => $entity->getApiKey(), 'verification_code' => $entity->getVerificationCode()]);

        if ($exists instanceof ApiCredentials){
            throw new \Exception('API key already exists');
        }

        $entity->setAccessMask($accessMask)
            ->setType($type);

        return $result;

    }

    public function updateActiveKey(Corporation $corporation, ApiCredentials $key){

    }

    public function buildInstanceFromRequest(ParameterBag $content){
        $creds = new ApiCredentials();

        $creds->setVerificationCode($content->get('verification_code'))
            ->setApiKey($content->get('api_key'));

        return $creds;
    }

    public function getClient(ApiCredentials $credentials, $scope = 'corp'){
        $client = $this->pheal->createEveOnline(
            $credentials->getApiKey(),
            $credentials->getVerificationCode()
        );

        $client->scope = $scope;

        return $client;
    }
}
