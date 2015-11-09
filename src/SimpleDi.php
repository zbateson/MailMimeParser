<?php
namespace ZBateson\MailMimeParser;

use ZBateson\MailMimeParser\Header\Consumer\ConsumerService;
use ZBateson\MailMimeParser\Header\HeaderFactory;

/**
 * Dependency injection container for use by ZBateson\MailMimeParser - because a
 * more complex one seems like overkill.
 * 
 * Constructs objects and whatever dependencies they require.
 *
 * @author Zaahid Bateson
 */
class SimpleDi
{
    /**
     * @var \ZBateson\MailMimeParser\PartFactory singleton 'service' instance
     */
    protected $partFactory;
    
    /**
     * @var \ZBateson\MailMimeParser\PartStreamRegistry singleton 'service'
     * instance
     */
    protected $partStreamRegistry;
    
    /**
     * @var \ZBateson\MailMimeParser\Header\HeaderFactory singleton 'service'
     * instance
     */
    protected $headerFactory;
    
    /**
     * @var \ZBateson\MailMimeParser\Header\Part\PartFactory singleton 'service'
     * instance
     */
    protected $headerPartFactory;
    
    /**
     * @var \ZBateson\MailMimeParser\Header\Consumer\ConsumerService singleton
     * 'service' instance
     */
    protected $consumerService;
    
    /**
     * Constructs a SimpleDi - call singleton() to invoke
     */
    private function __construct()
    {
    }
    
    /**
     * Returns the singleton instance.
     * 
     * @return \ZBateson\MailMimeParser\SimpleDi
     */
    public static function singleton()
    {
        static $singleton = null;
        if (empty($singleton)) {
            $singleton = new SimpleDi();
        }
        return $singleton;
    }
    
    /**
     * Returns a singleton 'service' instance for the given service named $var
     * with a class type of $class.
     * 
     * @param string $var the name of the service
     * @param string $class the name of the class
     * @return mixed the service object
     */
    protected function getInstance($var, $class)
    {
        if ($this->$var === null) {
            $this->$var = new $class();
        }
        return $this->$var;
    }
    
    /**
     * Constructs and returns a new MessageParser object.
     * 
     * @return \ZBateson\MailMimeParser\MessageParser
     */
    public function newMessageParser()
    {
        return new MessageParser(
            $this->newMessage(),
            $this->getPartFactory()
        );
    }
    
    /**
     * Constructs and returns a new Message object.
     * 
     * @return \ZBateson\MailMimeParser\Message
     */
    public function newMessage()
    {
        return new Message(
            $this->getHeaderFactory(),
            $this->getPartStreamRegistry()
        );
    }
    
    /**
     * Returns the part factory service instance.
     * 
     * @return \ZBateson\MailMimeParser\PartFactory
     */
    public function getPartFactory()
    {
        if ($this->partFactory === null) {
            $this->partFactory = new PartFactory(
                $this->getHeaderFactory()
            );
        }
        return $this->partFactory;
    }
    
    /**
     * Returns the header factory service instance.
     * 
     * @return \ZBateson\MailMimeParser\Header\HeaderFactory
     */
    public function getHeaderFactory()
    {
        if ($this->headerFactory === null) {
            $this->headerFactory = new HeaderFactory(
                $this->getConsumerService(),
                $this->getHeaderPartFactory()
            );
        }
        return $this->headerFactory;
    }
    
    /**
     * Returns the part stream registry service instance.  The method also
     * registers the stream extension by calling registerStreamExtensions.
     * 
     * @return \ZBateson\MailMimeParser\PartStreamRegistry
     */
    public function getPartStreamRegistry()
    {
        if ($this->partStreamRegistry === null) {
            $this->registerStreamExtensions();
        }
        return $this->getInstance('partStreamRegistry', __NAMESPACE__ . '\PartStreamRegistry');
    }
    
    /**
     * Returns the part factory service
     * 
     * @return \ZBateson\MailMimeParser\Header\Part\PartFactory
     */
    public function getHeaderPartFactory()
    {
        return $this->getInstance('headerPartFactory', __NAMESPACE__ . '\Header\Part\PartFactory');
    }
    
    /**
     * Returns the header consumer service
     * 
     * @return ZBateson\MailMimeParser\Header\Consumer\ConsumerService
     */
    public function getConsumerService()
    {
        if ($this->consumerService === null) {
            $this->consumerService = new ConsumerService($this->getHeaderPartFactory());
        }
        return $this->consumerService;
    }
    
    /**
     * Registers stream extensions for PartStream and CharsetStreamFilter
     * 
     * @see stream_filter_register
     * @see stream_wrapper_register
     */
    protected function registerStreamExtensions()
    {
        stream_filter_register(CharsetStreamFilter::STREAM_FILTER_NAME, __NAMESPACE__ . '\CharsetStreamFilter');
        stream_wrapper_register(PartStream::STREAM_WRAPPER_PROTOCOL, __NAMESPACE__ . '\PartStream');
    }
}
