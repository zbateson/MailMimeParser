<?php
namespace ZBateson\MailMimeParser\Message\Part\Factory;

use PHPUnit\Framework\TestCase;

/**
 * PartStreamFilterManagerFactoryTest
 *
 * @group PartStreamFilterManagerFactory
 * @group MessagePart
 * @covers ZBateson\MailMimeParser\Message\Part\Factory\PartStreamFilterManagerFactory
 * @author Zaahid Bateson
 */
class PartStreamFilterManagerFactoryTest extends TestCase
{
    protected $partStreamFilterManagerFactory;

    protected function setUp(): void
    {
        $mocksdf = $this->getMockBuilder('ZBateson\MailMimeParser\Stream\StreamFactory')
            ->getMock();
        $this->partStreamFilterManagerFactory = new PartStreamFilterManagerFactory(
            $mocksdf
        );
    }

    public function testNewInstance()
    {
        $manager = $this->partStreamFilterManagerFactory->newInstance();
        $this->assertInstanceOf(
            '\ZBateson\MailMimeParser\Message\Part\PartStreamFilterManager',
            $manager
        );
    }
}
