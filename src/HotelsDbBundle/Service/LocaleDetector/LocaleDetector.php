<?php


namespace Apl\HotelsDbBundle\Service\LocaleDetector;


use Apl\HotelsDbBundle\Entity\Locale;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleDetector
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Locale
     */
    private $defaultLocale;

    /**
     * @var \SplObjectStorage
     */
    private $requestLocaleCash;

    /**
     * LocaleDetector constructor.
     * @param RequestStack $requestStack
     * @param string $defaultLanguage
     * @param string $defaultCountry
     */
    public function __construct(RequestStack $requestStack, string $defaultLanguage)
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = new Locale(strtolower($defaultLanguage));
        $this->requestLocaleCash = new \SplObjectStorage();
    }

    /**
     * @return Locale
     */
    public function getCurrentLocale(): Locale
    {
        return $this->getRequestLocale() ?: $this->getDefaultLocale();
    }

    /**
     * @return Locale|null
     */
    public function getRequestLocale(): ?Locale
    {
        if ($currentRequest = $this->requestStack->getCurrentRequest()) {
            if (isset($this->requestLocaleCash[$currentRequest])) {
                $requestLocale = $this->requestLocaleCash[$currentRequest];
            } elseif ($requestLocale = $currentRequest->getLocale()) {
                $requestLocale = $this->requestLocaleCash[$currentRequest] = new Locale($requestLocale);
            }
        }

        return $requestLocale ?? null;
    }

    /**
     * @return Locale
     */
    public function getDefaultLocale(): Locale
    {
        return $this->defaultLocale;
    }
}