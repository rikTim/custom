<?php


namespace Apl\HotelsDbBundle\Service\EntityManagerProxy;


use Doctrine\ORM\Decorator\EntityManagerDecorator;

/**
 * Class EntityManagerProxy
 * @package Apl\HotelsDbBundle\Service
 *
 * This class using for resolve multiply connections.
 * All service in bundle using her force!
 */
class EntityManagerProxy extends EntityManagerDecorator
{
}