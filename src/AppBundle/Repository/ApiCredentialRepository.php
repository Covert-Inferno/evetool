<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Corporation;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ApiCredentialRepository extends EntityRepository {

    public function getActiveKeyForUser(User $user){
        return $this->createQueryBuilder('api')
            ->select('api')
            ->leftJoin('api.character', 'char')
            ->leftJoin('char.user', 'u')
            ->where('api.is_active = :active')
            ->andWhere('u = :user')
            ->setParameters(['user' => $user, 'active' => true])
            ->getQuery()->getResult();
    }

    public function getActiveKey(Corporation $corp){

        return $this->createQueryBuilder('api')
            ->select('api')
            ->where('api.is_active = :active')
            ->andWhere('api.corporation = :corp')
            ->setParameters(['corp' => $corp, 'active' => true])
            ->getQuery()->getOneOrNullResult();

    }
}
