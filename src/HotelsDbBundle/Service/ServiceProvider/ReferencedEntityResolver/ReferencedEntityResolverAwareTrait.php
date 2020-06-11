<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver;


trait ReferencedEntityResolverAwareTrait
{
    /**
     * @var ReferencedEntityResolver
     */
    protected $referencedEntityResolver;

    /**
     * @param ReferencedEntityResolver $referencedEntityResolver
     * @required
     */
    public function setReferencedEntityResolver(ReferencedEntityResolver $referencedEntityResolver): void
    {
        $this->referencedEntityResolver = $referencedEntityResolver;
    }
}