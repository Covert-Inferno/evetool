<?php

namespace AppBundle\Repository;


use AppBundle\Entity\Account;
use Carbon\Carbon;
use Doctrine\ORM\EntityRepository;

class AccountBalanceRepository extends EntityRepository {

    public function getLatestBalance(Account $acc){
        return $this->createQueryBuilder('ab')
            ->leftJoin('ab.account', 'acc')
            ->where('acc = :account')
            ->addOrderBy('ab.created_at', 'DESC')
            ->setMaxResults(1)
            ->setParameters(['account' => $acc])
            ->getQuery()->getOneOrNullResult();
    }

    public function getLastDayBalance(Account $acc){
        $date = Carbon::create()
            ->subDay();

        return $this->createQueryBuilder('ab')
            ->leftJoin('ab.account', 'acc')
            ->where('acc = :account')
            ->andWhere('ab.created_at < :date')
            ->addOrderBy('ab.created_at', 'DESC')
            ->setMaxResults(1)
            ->setParameters(['account' => $acc, 'date' => $date])
            ->getQuery()->getOneOrNullResult();

    }

    public function getOrderedBalances(Account $acc){
        return $this->createQueryBuilder('ab')
            ->leftJoin('ab.account', 'acc')
            ->where('acc = :account')
            ->addOrderBy('ab.created_at', 'DESC')
            ->setParameters(['account' => $acc])
            ->getQuery()->getResult();

    }

    public function getOrderedBalancesByDate(Account $acc, Carbon $date){
        $start = $date->startOfDay();
        $end = $date->copy()->endOfDay();

        return $this->createQueryBuilder('ab')
            ->leftJoin('ab.account', 'acc')
            ->where('acc = :account')
            ->andWhere('ab.created_at >= :start')
            ->andWhere('ab.created_at <= :end')
            ->addOrderBy('ab.created_at', 'ASC')
            ->setParameters([
                'account' => $acc,
                'start' => $start,
                'end' => $end
            ])
            ->getQuery()->getResult();

    }
}
