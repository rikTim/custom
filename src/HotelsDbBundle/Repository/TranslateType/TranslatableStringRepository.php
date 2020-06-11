<?php


namespace Apl\HotelsDbBundle\Repository\TranslateType;


class TranslatableStringRepository extends AbstractTranslateTypeRepository
{
    protected function getSearchFieldName(): string
    {
        return 'nominative';
    }
}