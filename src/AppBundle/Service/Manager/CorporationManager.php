<?php

namespace AppBundle\Service\Manager;

use AppBundle\Entity\Account;
use AppBundle\Entity\ApiCredentials;
use AppBundle\Entity\Corporation;
use AppBundle\Entity\CorporationDetail;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tarioch\PhealBundle\DependencyInjection\PhealFactory;

class CorporationManager implements DataManagerInterface {

    private $pheal;
    private $doctrine;
    private $api_manager;
    private $log;

    public function __construct(PhealFactory $pheal, Registry $registry, ApiKeyManager $apiManager, Logger $logger){
        $this->pheal = $pheal;
        $this->doctrine = $registry;
        $this->api_manager = $apiManager;
        $this->log = $logger;
    }

    public function buildInstanceFromRequest(ParameterBag $content){
        $corp = new Corporation();

        $creds = $this->api_manager->buildInstanceFromRequest($content);
        $creds->setIsActive(true);

        $corp->addApiCredential($creds);

        return $corp;
    }

    public function getCorporationDetails(Corporation $corporation){

        $apiKey = $this->getApiKey($corporation);

        $client = $this->getClient($apiKey, 'account');
        $details = $client->APIKeyInfo()->key->characters[0];
        $result =  [ 'id' => $details->characterID ];


        return $result;
    }

    public function getMembers(Corporation $corporation){
        $apiKey = $this->getApiKey($corporation);

        $client = $this->getClient($apiKey);

        $members = $client->MemberTracking()->members;

        var_dump($members);die;
    }

    public function getCorporationSheet(Corporation $corporation){
        $apiKey = $this->getApiKey($corporation);
        $corpClient = $this->getClient($apiKey);

        $corpSheet = $corpClient->CorporationSheet();

        $this->initializeAccounts($corpSheet->walletDivisions, $corporation);

        if (!($entity = $corporation->getCorporationDetails()) instanceof CorporationDetail){
            $entity = new CorporationDetail();
        }

        $entity->setName($corpSheet->corporationName)
            ->setTicker($corpSheet->ticker)
            ->setCeoName($corpSheet->ceoName)
            ->setCeoId($corpSheet->ceoID)
            ->setHeadquartersId($corpSheet->stationID)
            ->setHeadquartersName($corpSheet->stationName)
            ->setDescription($corpSheet->description)
            ->setUrl($corpSheet->url)
            ->setAllianceId($corpSheet->allianceID)
            ->setAllianceName($corpSheet->allianceName)
            ->setTaxRate($corpSheet->taxRate)
            ->setMemberCount($corpSheet->memberCount)
            ->setMemberLimit($corpSheet->memberLimit)
            ->setShares($corpSheet->shares);

        return $entity;

    }

    public function initializeAccounts($accounts, Corporation $corp){
        foreach ($accounts as $a){
            if (intval($a->accountKey) <= 1006) {
                $exists = $this->doctrine->getRepository('AppBundle:Account')
                    ->findOneBy(['corporation' => $corp, 'division' => $a->accountKey]);


                if ($exists instanceof Account){
                    $exists->setName($a->description);
                } else {
                    $account = new Account();

                    $account->setDivision($a->accountKey)
                        ->setName($a->description);

                    $corp->addAccount($account);

                }
            }
        }
    }

    public function getClient(ApiCredentials $key, $scope = 'corp'){

        $client = $this->pheal->createEveOnline(
            $key->getApiKey(),
            $key->getVerificationCode()
        );

        $client->scope = $scope;

        return $client;

    }

    protected function getApiKey(Corporation $entity){

        $apiKey = $this->doctrine->getRepository('AppBundle:ApiCredentials')
            ->getActiveKey($entity);

        if ($apiKey === null){
            throw new \Exception('No active api key for corp' . $entity->getId() .' found');
        }

        return $apiKey;
    }

}