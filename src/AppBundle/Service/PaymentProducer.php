<?php

namespace AppBundle\Service;

use Mullenlowe\CommonBundle\Component\AMQP\CrudProducer;

/**
 * Class PaymentProducer
 * @package AppBundle\Service\PaymentProducer
 */
class PaymentProducer
{
    const CONTEXT = 'Lead';
    const ACTION = 'update';
    const ROUTING_KEY = 'crud.payment';

    /**
     * @var CrudProducer
     */
    private $producer;

    /**
     * PaymentProducer constructor.
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
            ['data' => $data],
            self::CONTEXT,
            self::ACTION,
            self::ROUTING_KEY
        );
    }
}
