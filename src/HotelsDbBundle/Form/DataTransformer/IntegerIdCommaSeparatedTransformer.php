<?php


namespace Apl\HotelsDbBundle\Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class IntegerIdCommaSeparatedTransformer
 *
 * @package Apl\HotelsDbBundle\Form\DataTransformer
 */
class IntegerIdCommaSeparatedTransformer implements DataTransformerInterface
{

    /**
     * @param mixed $parameterArray
     * @return null|string
     */
    public function transform($parameterArray)
    {
        return \is_array($parameterArray) ? implode(',', $parameterArray) : null;
    }

    /**
     * @param mixed $parameterString
     * @return int[]
     */
    public function reverseTransform($parameterString)
    {
        if ($parameterString === null) {
            return [];
        }

        if (!is_scalar($parameterString)) {
            throw new TransformationFailedException(sprintf('Incorrect parameter type: excepted string, "%s" given', \gettype($parameterString)));
        }

        $parameterArray = [];
        foreach (explode(',', $parameterString) as $value) {
            $parameterArray[] = (int)trim($value);
        }

        return array_unique($parameterArray);
    }
}