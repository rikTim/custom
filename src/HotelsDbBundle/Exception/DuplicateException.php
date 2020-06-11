<?php


namespace Base\HotelsDbBundle\Exception;
use Throwable;

/**
 * Class DuplicateException
 * @package Base\HotelsDbBundle\Exception
 */
class DuplicateException extends \InvalidArgumentException implements ExceptionInterface
{
    /**
     * @var mixed
     */
    private $duplicate;

    /**
     * DuplicateException constructor.
     * @param string $message
     * @param $duplicate
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', $duplicate, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->duplicate = $duplicate;
    }

    /**
     * @return mixed
     */
    public function getDuplicate()
    {
        return $this->duplicate;
    }
}