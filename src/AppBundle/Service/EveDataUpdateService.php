<?php

namespace AppBundle\Service;

use AppBundle\Entity\ApiUpdate;
use AppBundle\Entity\Corporation;
use AppBundle\Entity\CorporationDetail;
use AppBundle\Service\DataManager\AccountManager;
use AppBundle\Service\DataManager\AssetManager;
use AppBundle\Service\DataManager\CorporationManager;
use AppBundle\Service\DataManager\JournalTransactionManager;
use AppBundle\Service\DataManager\MarketOrderManager;
use AppBundle\Service\DataManager\MarketTransactionManager;
use AppBundle\Service\DataManager\StarbaseManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Monolog\Logger;

class EveDataUpdateService {

    protected $doctrine;

    protected $registry;

    protected $log;

    public function __construct(DataManagerRegistry $registry, Registry $doctrine, Logger $log){
        $this->doctrine = $doctrine;
        $this->registry = $registry;
        $this->log = $log;
    }

    public function checkCorporationDetails(Corporation $c){
        $em = $this->doctrine->getManager();
        if ($c->getEveId() === null){
            $result = $this->corp_manager->getCorporationDetails($c);

            $c->setEveId($result['id']);
        }

        if (!$c->getCorporationDetails() instanceof CorporationDetail) {
            $result = $this->corp_manager->getCorporationSheet($c);

            $c->setCorporationDetails($result);
        }

        $em->persist($c);
        $em->flush();
    }

    public function updateShortTimerCalls(Corporation $c, $force = false){
        $calls = [
            AccountManager::getName() => 'updateAccounts',
            CorporationManager::getName() => ['getCorporationSheet', 'getMembers'],
            JournalTransactionManager::getName() => 'updateJournalTransactions',
            MarketTransactionManager::getName() => 'updateMarketTransactions',
            StarbaseManager::getName() => 'getStarbases'
        ];

        foreach ($calls as $manager => $call){
            if (is_array($call)){
                foreach ($call as $ic){
                    if(!$this->checkShortTimer($c, $ic) || $force === true) {
                        $this->doUpdate($manager, $ic, $c, ApiUpdate::CACHE_STYLE_SHORT);
                    }
                }
            } else {
                if(!$this->checkShortTimer($c, $call) || $force === true) {
                    $this->doUpdate($manager, $call, $c, ApiUpdate::CACHE_STYLE_SHORT);
                }
            }
        }
    }

    public function updateLongTimerCalls(Corporation $c, $force = false){
        $calls = [
            AssetManager::getName() => 'generateAssetList',
            MarketOrderManager::getName() => 'getMarketOrders'
        ];

        foreach ($calls as $manager => $call){
            if(!$this->checkLongTimer($c, $call) || $force === true){
                $this->doUpdate($manager, $call, $c, ApiUpdate::CACHE_STYLE_LONG);
            }
        }

    }

    public function checkShortTimer(Corporation $c, $call){
        return $this->doctrine->getRepository('AppBundle:ApiUpdate')
            ->getShortTimerExpired($c, $call);
    }

    public function checkLongTimer(Corporation $c, $call){
        return $this->doctrine->getRepository('AppBundle:ApiUpdate')
            ->getLongTimerExpired($c, $call);
    }

    public function updateAssetCache(Corporation $c){
        $this->log->debug(sprintf("Updating asset group cache for"));
        $this->registry->get(AssetManager::getName())
            ->updateAssetGroupCache($c);
    }


    public function createApiUpdate($type, $call, $success){
        $access = new ApiUpdate();

        $access->setType($type)
            ->setApiCall($call)
            ->setSucceeded($success);

        return $access;
    }

    protected function doUpdate($manager, $call, Corporation $c, $cache_style){
        $success = $this->tryCall($manager, $call, $c);

        $em = $this->doctrine->getManager();

        $update = $this->createApiUpdate(
            $cache_style,
            $this->resolveCall($call),
            $success
        );

        $c->addApiUpdate($update);

        $em->persist($c);
        $em->flush();
    }

    protected function tryCall($manager, $function, $arg){
        try {
            $this->registry
                ->get($manager)
                ->$function($arg);

            return true;
        } catch (\Exception $e){
            $this->log->error(sprintf("Error syncing data for %s  on call %s with: %s",
                $arg->getCorporationDetails()->getName(),
                $function,
                $e->getMessage()
            ));

            return false;
        }
    }

    protected function resolveCall($call){
        switch ($call){
            case 'updateAccounts':
                return ApiUpdate::CORP_ACC_BALANCES;
            case 'updateJournalTransactions':
                return ApiUpdate::CORP_WALLET_JOURNAL;
            case 'updateMarketTransactions':
                return ApiUpdate::CORP_WALLET_TRANSACTION;
            case 'generateAssetList':
                return ApiUpdate::CORP_ASSET_LIST;
            case 'getMarketOrders':
                return ApiUpdate::CORP_MARKET_ORDERS;
            case 'getStarbases':
                return ApiUpdate::CORP_STARBASE_LIST;
            case 'getMembers':
                return ApiUpdate::CORP_MEMBERS;
            case 'getCorporationSheet':
                return ApiUpdate::CORP_DETAILS;
            case 'updateRefTypes':
                return ApiUpdate::REF_TYPES;
            case 'updateConquerableStations':
                return ApiUpdate::CONQUERABLE_STATIONS;
        }
    }
}
