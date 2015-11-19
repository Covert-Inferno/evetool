<?php


namespace AppBundle\Service;

use AppBundle\Service\Manager\AbstractManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use EveBundle\Entity\AveragePrice;
use EveBundle\Repository\Registry as EveRegistry;
use Tarioch\PhealBundle\DependencyInjection\PhealFactory;

class PriceUpdateManager extends AbstractManager {

    private $cache;

    public function __construct(PhealFactory $pheal, Registry $doctrine, EveRegistry $registry){
        parent::__construct($pheal, $doctrine, $registry);

        $this->cache = new ArrayCollection();
    }

    public function updatePrices(array $items){
        $arrType = [];
        foreach ($items as $i){
            if (is_object($i)){
                $this->doUpdate($i);
            } elseif (is_array($i)) {
                $arrType[] = $this->doUpdate($i, false);
            }
        }

        if (count($arrType)){
            return $arrType;
        }

        return $items;
    }

    protected function doUpdate($i, $object = true){
        $prices = $this->doctrine->getManager('eve_data')
            ->getRepository('EveBundle:AveragePrice');

        $typeId = $object
            ? $i->getTypeId()
            : $i['typeID'];

        $descriptors = $object
            ? $i->getDescriptors()
            : [];

        if (!$this->hasItem($typeId)){
            $price = $prices->getAveragePriceByType($typeId);
            if ($price instanceof AveragePrice){
                $this->cacheItem($typeId, $price->getAveragePrice());

                return $this->updateItemPrice($i, $price, $descriptors);
            }
        }

        return $this->updateItemPrice($i, $this->cache[$typeId], $descriptors);
    }

    protected function updateItemPrice($i, $price = null, array &$descriptors = []){
        $price = $price instanceof AveragePrice
            ? floatval($price->getAveragePrice())
            : 0;

        if (is_object($i)) {
            $descriptors['price'] = $price;
            $descriptors['total_price'] = floatval($descriptors['price'] * $i->getQuantity());
            $i->setDescriptors($descriptors);
        } else {
            $i['price'] = $price;

            return $i;
        }
    }

    protected function cacheItem($id, $price){
        if (!isset($this->cache[$id])){
            $this->cache[$id] = $price;
        }
    }

    protected function hasItem($id){
        return isset($this->cache[$id]);

    }

    protected function getItem($id){
        return $this->cache[$id];
    }
}
