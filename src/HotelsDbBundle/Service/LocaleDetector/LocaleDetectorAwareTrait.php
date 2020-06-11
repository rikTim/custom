<?php


namespace Apl\HotelsDbBundle\Service\LocaleDetector;


trait LocaleDetectorAwareTrait
{
    /**
     * @var LocaleDetector
     */
    protected $localeDetector;

    /**
     * @param LocaleDetector $localeDetector
     * @required
     */
    public function setLocaleDetector(LocaleDetector $localeDetector): void
    {
        $this->localeDetector = $localeDetector;
    }
}