<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssetRepository")
 * @ORM\Table(name="assets")
 * @JMS\ExclusionPolicy("all")
 *
 * @package AppBundle\Entity
 */
class Asset
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    protected $id;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     * @JMS\Expose()
     */
    protected $itemId;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     * @JMS\Expose()
     */
    protected $locationId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\Expose()
     */
    protected $typeId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\Expose()
     */
    protected $quantity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\Expose()
     */
    protected $flag;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @JMS\Expose()
     */
    protected $singleton;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AssetGroup", inversedBy="assets")
     */
    protected $asset_group;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set itemId
     *
     * @param integer $itemId
     * @return Asset
     */
    public function setItemId($itemId)
    {
        $this->itemId = intval($itemId);

        return $this;
    }

    /**
     * Get itemId
     *
     * @return integer 
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set locationId
     *
     * @param integer $locationId
     * @return Asset
     */
    public function setLocationId($locationId)
    {
        $this->locationId = intval($locationId);

        return $this;
    }

    /**
     * Get locationId
     *
     * @return integer 
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Set typeId
     *
     * @param integer $typeId
     * @return Asset
     */
    public function setTypeId($typeId)
    {
        $this->typeId = intval($typeId);

        return $this;
    }

    /**
     * Get typeId
     *
     * @return integer 
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Asset
     */
    public function setQuantity($quantity)
    {
        $this->quantity = intval($quantity);

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set flag
     *
     * @param integer $flag
     * @return Asset
     */
    public function setFlag($flag)
    {
        $this->flag = intval($flag);

        return $this;
    }

    /**
     * Get flag
     *
     * @return integer 
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * Set singleton
     *
     * @param integer $singleton
     * @return Asset
     */
    public function setSingleton($singleton)
    {
        $this->singleton = (bool)$singleton;

        return $this;
    }

    /**
     * Get singleton
     *
     * @return integer 
     */
    public function getSingleton()
    {
        return $this->singleton;
    }

    /**
     * Set asset_group
     *
     * @param \AppBundle\Entity\AssetGroup $assetGroup
     * @return Asset
     */
    public function setAssetGroup(\AppBundle\Entity\AssetGroup $assetGroup = null)
    {
        $this->asset_group = $assetGroup;

        return $this;
    }

    /**
     * Get asset_group
     *
     * @return \AppBundle\Entity\AssetGroup
     */
    public function getAssetGroup()
    {
        return $this->asset_group;
    }
}
