<?php

namespace Mouf\AmqpClient;

use ErrorException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use Mouf\AmqpClient\Exception\ConnectionException;
use PhpAmqpLib\Exception\AMQPIOException;

class Client {
    /**
     * RabbitMq host.
     *
     * @var string
     */
    private $host;

    /**
     * RabbitMq port.
     *
     * @var string
     */
    private $port;

    /**
     * RabbitMq user.
     *
     * @var string
     */
    private $user;

    /**
     * RabbitMq password.
     *
     * @var string
     */
    private $password;

    /**
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @var int
     */
    private $prefetchSize = null;

    /**
     * It's for QOS prefetch-count http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @var int
     */
    private $prefetchCount = null;

    /**
     * It's for QOS global http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @var int
     */
    private $aGlobal = null;

    /**
     * RabbitMq connection.
     *
     * @var AbstractConnection
     */
    private $connection = null;

    /**
     * RabbitMq channel.
     *
     * @var \AMQPChannel
     */
    private $channel = null;

    /**
     * List of RabbitMq object.
     *
     * @var RabbitMqObjectInterface[]
     */
    private $rabbitMqObjects = [];

    /**
     * @var string
     */
    private $vhost = '/';

    /**
     * Use a secure (https) connection to the queue?
     * @var bool
     */
    private $secure;

    /**
     * SSL Options
     * @var array
     */
    private $sslOptions = [
        'verify_peer_name' => true
    ];

    public function __construct($host, $port, $user, $password, $vhost = '/', $secure = false) {
        $this->host = $host;
        $this->port = ($port !== null) ? $port : 5672;
        $this->user = $user;
        $this->password = $password;
        $this->vhost = $vhost;
        $this->secure = $secure;
    }

    /**
     * Get prefetch size for QOS.
     */
    public function getPrefetchSize() {
        return $this->prefetchSize;
    }

    /**
     * Set prefetch size
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @param int $prefetchSize
     *
     * @return Client
     */
    public function setPrefetchSize($prefetchSize) {
        $this->prefetchSize = $prefetchSize;

        return $this;
    }

    /**
     * Get prefetch count for QOS.
     */
    public function getPrefetchCount() {
        return $this->prefetchCount;
    }

    /**
     * Set prefetch size
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @param int $prefetchCount
     *
     * @return Client
     */
    public function setPrefetchCount($prefetchCount) {
        $this->prefetchCount = $prefetchCount;

        return $this;
    }

    /**
     * Get a global for QOS.
     */
    public function getAGlobal() {
        return $this->aGlobal;
    }

    /**
     * Set global
     * It's for QOS prefetch-size http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.
     *
     * @param int $aGlobal
     *
     * @return Client
     */
    public function setAGlobal($aGlobal) {
        $this->aGlobal = $aGlobal;

        return $this;
    }

    /**
     * Set RabbitMq object.
     *
     * @param RabbitMqObjectInterface[] $rabbitMqObjects
     */
    public function setRabbitMqObjects(array $rabbitMqObjects) {
        $this->rabbitMqObjects = $rabbitMqObjects;
    }

    public function register(RabbitMqObjectInterface $object) {
        if (!in_array($object, $this->rabbitMqObjects, true)) {
            $this->rabbitMqObjects[] = $object;
        }
    }

    /**
     * Connection to the RabbitMq service with AMQPStreamConnection.
     *
     * @return AMQPChannel
     * @throws ConnectionException
     * @throws ErrorException
     */
    public function getChannel() {
        if (!$this->connection) {
            try {
                if ($this->secure) {
                    $this->connection = new AMQPSSLConnection($this->host, $this->port, $this->user, $this->password, $this->vhost, $this->sslOptions);
                }
                else {
                    if (function_exists('socket_create')) {
                        $this->connection = new AMQPSocketConnection($this->host, $this->port, $this->user, $this->password, $this->vhost, false, 'AMQPLAIN', null, 'en_US', 0);
                    }
                    else {
                        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password, $this->vhost);
                    }
                }
            } catch (ErrorException $e) {
                /* We are trying to catch the exception when the connection if refused */
                if (preg_match("/.*unable to connect.*Connection refused.*/", $e->__toString())) {
                    throw new ConnectionException("Cannot create the connection", 404, $e);
                }
                throw $e;
            } catch (AMQPIOException $e) {
                throw new ConnectionException("Cannot create the connection", 404, $e);
            }
            $this->channel = $this->connection->channel();

            if ($this->prefetchSize !== null || $this->prefetchCount !== null || $this->aGlobal !== null) {
                $this->channel->basic_qos($this->prefetchSize, $this->prefetchCount, $this->aGlobal);
            }

            foreach ($this->rabbitMqObjects as $rabbitMqObject) {
                $rabbitMqObject->init($this->channel);
            }
        }

        return $this->channel;
    }

    /**
     * Closes the connection
     * @return mixed The response from the connection close command
     */
    public function closeConnection() {
        $return = $this->connection->close();
        $this->connection = null;
        return $return;
    }

    /**
     * Returns the list of registered queues.
     *
     * @return QueueInterface[]
     */
    public function getQueues() {
        return array_filter($this->rabbitMqObjects, function (RabbitMqObjectInterface $object) {
            return $object instanceof QueueInterface;
        });
    }

    /**
     * @return array
     */
    public function getSslOptions(): array {
        return $this->sslOptions;
    }

    /**
     * @param array $sslOptions
     */
    public function setSslOptions(array $sslOptions): void {
        $this->sslOptions = $sslOptions;
    }
}
