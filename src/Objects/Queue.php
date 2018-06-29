<?php

namespace Mouf\AmqpClient\Objects;

use Mouf\AmqpClient\Client;
use Mouf\AmqpClient\QueueInterface;
use Mouf\AmqpClient\RabbitMqObjectInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Mouf\AmqpClient\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Marc
 */
class Queue implements RabbitMqObjectInterface, QueueInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Queue name.
     *
     * @var string
     */
    private $name;

    /**
     * Passive.
     *
     * @var bool
     */
    private $passive = false;

    /**
     * Durable.
     *
     * @var bool
     */
    private $durable = false;

    /**
     * Exclusive.
     *
     * @var bool
     */
    private $exclusive = false;

    /**
     * Auto delete.
     *
     * @var bool
     */
    private $autoDelete = false;

    /**
     * No wait.
     *
     * @var bool
     */
    private $noWait = false;

    /**
     * Ticket.
     *
     * @var int
     */
    private $ticket = null;

    /**
     * RabbitMq specific parameter : x-dead-letter-exchange.
     *
     * @var Exchange
     */
    private $deadLetterExchanger = null;

    /**
     * RabbitMq specific parameter : confirm.
     *
     * @var int
     */
    private $confirm = null;

    /**
     * RabbitMq specific parameter : consumer_cancel_notify.
     *
     * @var bool
     */
    private $consumerCancelNotify = null;

    /**
     * RabbitMq specific parameter : alternate-exchange.
     *
     * @var Queue
     */
    private $alternateExchange = null;

    /**
     * RabbitMq specific parameter : x-message-ttl.
     *
     * @var int
     */
    private $ttl = null;

    /**
     * RabbitMq specific parameter : x-max-length.
     *
     * @var int
     */
    private $maxLength = null;

    /**
     * RabbitMq specific parameter : x-max-priority.
     *
     * @var int
     */
    private $maxPriority = null;

    /**
     * Parameter to initialize object only one time.
     *
     * @var bool
     */
    private $init = false;

    /**
     * Consumer list implement ConsumerInterface.
     *
     * @var array
     */
    private $consumers;

	/**
	 * @var array
	 */
	private $arguments;

	/**
     * Set the source (Binding).
     *
     * @param Client              $client
     * @param string              $name
     * @param ConsumerInterface[] $consumers
     */
    public function __construct(Client $client, $name, array $consumers = [])
    {
        $this->client = $client;
        $this->client->register($this);
        $this->name = $name;
        $this->consumers = $consumers;
    }

    /**
     * Get queue name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get passive.
     *
     * @return bool
     */
    public function getPassive()
    {
        return $this->passive;
    }

    /**
     * @param bool $passive
     *
     * @return Queue
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;

        return $this;
    }

    /**
     * Get durable.
     *
     * @return bool
     */
    public function getDurable()
    {
        return $this->durable;
    }

    /**
     * Set durable.
     *
     * @param bool $durable
     *
     * @return Queue
     */
    public function setDurable($durable)
    {
        $this->durable = $durable;

        return $this;
    }

    /**
     * Get exclusive.
     *
     * @return bool
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * Set exclusive.
     *
     * @param bool $exclusive
     *
     * @return Queue
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    /**
     * Get autoDelete.
     *
     * @return bool
     */
    public function getAutoDelete()
    {
        return $this->autoDelete;
    }

    /**
     * Set autoDelete.
     *
     * @param bool $autoDelete
     *
     * @return Queue
     */
    public function setAutoDelete($autoDelete)
    {
        $this->autoDelete = $autoDelete;

        return $this;
    }

    /**
     * Get noWait.
     *
     * @return bool
     */
    public function getNoWait()
    {
        return $this->noWait;
    }

    /**
     * Set noWait.
     *
     * @param bool $noWait
     *
     * @return Queue
     */
    public function setNoWait($noWait)
    {
        $this->noWait = $noWait;

        return $this;
    }

    /**
     * Get arguments.
     *
     * @return array|null
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set arguments.
     *
     * @param array $arguments
     *
     * @return Queue
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get ticket.
     *
     * @return int
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set ticket.
     *
     * @param int $ticket
     *
     * @return Queue
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get RabbitMq specific parameter : dead letter queue.
     *
     * @return Exchange
     */
    public function getDeadLetterExchanger()
    {
        return $this->deadLetterExchanger;
    }

    /**
     * Set RabbitMq specific parameter : dead letter queue.
     *
     * @param Exchange $exchange
     *
     * @return Queue
     */
    public function setDeadLetterExchange(Exchange $exchange)
    {
        $this->deadLetterExchanger = $exchange;

        return $this;
    }

    /**
     * Get RabbitMq specific parameter : confirm.
     *
     * @return int
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * Set RabbitMq specific parameter : confirm.
     *
     * @param int $confirm
     *
     * @return Queue
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * Get RabbitMq specific parameter : consumer_cancel_notify.
     *
     * @return bool
     */
    public function getConsumerCancelNotify()
    {
        return $this->consumerCancelNotify;
    }

    /**
     * Set RabbitMq specific parameter : consumer_cancel_notify.
     *
     * @param Queue $consumerCancelNotify
     *
     * @return Queue
     */
    public function setConsumerCancelNotify(Queue $consumerCancelNotify)
    {
        $this->consumerCancelNotify = $consumerCancelNotify;

        return $this;
    }

    /**
     * Get RabbitMq specific parameter : alternate_exchange.
     *
     * @return Queue
     */
    public function getAlternateExchange()
    {
        return $this->alternateExchange;
    }

    /**
     * Set RabbitMq specific parameter : alternate_exchange.
     *
     * @param Queue $alternateExchange
     *
     * @return Queue
     */
    public function setAlternateExchange(Queue $alternateExchange)
    {
        $this->alternateExchange = $alternateExchange;

        return $this;
    }

    /**
     * Get RabbitMq specific parameter : ttl.
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set RabbitMq specific parameter : ttl.
     *
     * @param int $ttl
     *
     * @return Queue
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get RabbitMq specific parameter : max length.
     *
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set RabbitMq specific parameter : max length.
     *
     * @param int $maxLength
     *
     * @return Queue
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * Get RabbitMq specific parameter : max priority.
     *
     * @return int
     */
    public function getMaxPriority()
    {
        return $this->maxPriority;
    }

    /**
     * Set RabbitMq specific parameter : max priority.
     *
     * @param int $maxPriority
     *
     * @return Queue
     */
    public function setMaxPriority($maxPriority)
    {
        $this->maxPriority = $maxPriority;

        return $this;
    }

    public function init(AMQPChannel $amqpChannel)
    {
        if (!$this->init) {
            if ($this->deadLetterExchanger) {
                $this->deadLetterExchanger->init($amqpChannel);
            }

            $parameters = [];
            if ($this->alternateExchange !== null) {
                $parameters['alternate-exchange'] = ['S', $this->alternateExchange->getName()];
            }
            if ($this->confirm !== null) {
                $parameters['confirm'] = ['I', $this->confirm];
            }
            if ($this->consumerCancelNotify !== null) {
                $parameters['consumer_cancel_notify'] = ['I', $this->consumerCancelNotify];
            }
            if ($this->deadLetterExchanger !== null) {
                $parameters['x-dead-letter-exchange'] = ['S', $this->deadLetterExchanger->getName()];
            }
            if ($this->maxLength) {
                $parameters['x-max-length'] = ['I', $this->maxLength];
            }
            if ($this->maxPriority) {
                $parameters['x-max-priority'] = ['I', $this->maxPriority];
            }
            if ($this->ttl) {
                $parameters['x-message-ttl'] = ['I', $this->ttl];
            }

            if (!$parameters) {
                $parameters = null;
            }
            // Don't declare the reserved amq. queues (autogenerated names)
			// @see http://rubybunny.info/articles/queues.html#reserved_queue_name_prefix
            if (substr($this->name, 0, 4) !== 'amq.') {
				$amqpChannel->queue_declare($this->name, $this->passive, $this->durable, $this->exclusive, $this->autoDelete, $this->noWait, $parameters);
			}
            $this->init = true;
        }
    }

    /**
     * Sends to RabbitMQ the order to subscribe to the consumers.
     */
    public function consume()
    {
        $channel = $this->client->getChannel();

        foreach ($this->consumers as $consumer) {
            /* @var $consumer ConsumerInterface */
            $channel->basic_consume($this->name,
                                    $consumer->getConsumerTag(),
                                    $consumer->isNoLocal(),
                                    $consumer->isNoAck(),
                                    $consumer->isExclusive(),
                                    $consumer->isNoWait(),
                                    function (AMQPMessage $msg) use ($consumer) {
                                        $consumer->callback($msg);
                                    },
                                    $consumer->getTicket(),
                                    $consumer->getArguments());
        }
    }

    /**
     * Unsubscribes consumers.
     */
    public function cancelConsume()
    {
        $channel = $this->client->getChannel();

        foreach ($this->consumers as $consumer) {
            /* @var $consumer ConsumerInterface */
            $channel->basic_cancel($consumer->getConsumerTag(),
                $consumer->isNoWait());
        }
    }

    /**
     * Sends a message directly to this queue (skipping any exchange).
     *
     * This is a RabbitMQ only feature. Behind the scene, it will dispatch the message to the default RabbitMQ exchange.
     *
     * @param Message $message
     * @param bool $mandatory
     * @param bool $immediate
     * @param null $ticket
     */
    public function publish(Message $message, $mandatory = false,
                            $immediate = false,
                            $ticket = null)
    {
        $channel = $this->client->getChannel();

        $channel->basic_publish($message->toAMQPMessage(), '', $this->name, $mandatory, $immediate, $ticket);
    }
}
