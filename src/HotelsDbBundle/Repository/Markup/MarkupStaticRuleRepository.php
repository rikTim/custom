<?php


namespace Apl\HotelsDbBundle\Repository\Markup;


use Apl\HotelsDbBundle\Entity\Location\Country;
use Apl\HotelsDbBundle\Entity\Location\Destination;
use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Doctrine\ORM\EntityRepository;
use HotelsBundle\Entity\Markup\MarkupStaticRule;

/**
 * Class MarkupStaticRuleRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Markup
 */
class MarkupStaticRuleRepository extends EntityRepository
{
    /**
     * @param Country[] $countries
     * @param Destination[] $rootDestination
     * @param ServiceProviderAlias[] $serviceProviderAliases
     * @return MarkupStaticRule[] array
     */
    public function findRules(array $countries, array $rootDestination, array $serviceProviderAliases): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.country IN (:countries) OR r.country IS NULL')
            ->andWhere('r.rootDestination IN (:destinations) OR r.rootDestination IS NULL')
            ->andWhere('r.serviceProviderAlias.alias IN (:providerAliases) OR r.serviceProviderAlias.alias IS NULL')
            ->setParameters([
                'countries' => $countries,
                'destinations' => $rootDestination,
                'providerAliases' => array_reduce($serviceProviderAliases, function (array $acc, ServiceProviderAlias $alias) {
                    $acc[] = $alias->getAlias();
                    return $acc;
                }, [])
            ])
            ->getQuery()
            ->getResult();
    }
}