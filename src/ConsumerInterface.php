<?php

namespace Mouf\AmqpClient;

use PhpAmqpLib\Message\AMQPMessage;

interface ConsumerInterface
{
	/**
	 * Callback for the consume service call if a message is receive.
	 *
	 * @param AMQPMessage $message
	 */
    public function callback(AMQPMessage $message);

    /**
     * Consumer tag to listen message
     * By default you can set it to ''.
     */
    public function getConsumerTag();

    /**
     * No local to listen message
     * By default you can set it to false.
     */
    public function isNoLocal();

    /**
     * No ack to listen message. RabbitMq remove the message only if an ack is send
     * By default you can set it to false.
     */
    public function isNoAck();

    /**
     * Exclusive to listen message
     * By default you can set it to false.
     */
    public function isExclusive();

    /**
     * No wait to listen message
     * By default you can set it to false.
     */
    public function isNoWait();

    /**
     * Argument to listen message
     * By default you can set it to [].
     */
    public function getArguments();

    /**
     * Ticket to listen message.
     */
    public function getTicket();
}
