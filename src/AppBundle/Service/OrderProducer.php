<?php

namespace AppBundle\Service;

use Mullenlowe\CommonBundle\Component\AMQP\CrudProducer;

/**
 * Class OrderProducer
 * @package AppBundle\Service
 */
class OrderProducer
{
    const CONTEXT = 'Order';
    const ACTION = 'update';
    const ROUTING_KEY = 'crud.order';

    /**
     * @var CrudProducer
     */
    private $producer;

    /**
     * OrderProducer constructor.
     *
     * @param CrudProducer $producer
     */
    public function __construct(CrudProducer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @param $data
     */
    public function publish($data)
    {
        $this->producer->publishJson(
            $data,
            self::CONTEXT,
            self::ACTION,
            self::ROUTING_KEY
        );
    }
}
