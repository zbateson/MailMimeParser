<?php
namespace ZBateson\MailMimeParser\Message\Factory;

use LegacyPHPUnit\TestCase;
use GuzzleHttp\Psr7;

/**
 * MimePartFactoryTest
 *
 * @group MimePartFactory
 * @group MessagePart
 * @covers ZBateson\MailMimeParser\Parser\Part\MimePartFactory
 * @covers ZBateson\MailMimeParser\Parser\Part\MessagePartFactory
 * @author Zaahid Bateson
 */
class MimePartFactoryTest extends TestCase
{
    private function getMockForFactoryExpectsOnce($factoryCls, $obCls)
    {
        $fac = $this->getMockBuilder($factoryCls)
            ->disableOriginalConstructor()
            ->getMock();
        $ob = $this->getMockBuilder($obCls)
            ->disableOriginalConstructor()
            ->getMock();
        $fac->expects($this->once())->method('newInstance')->willReturn($ob);
        return $fac;
    }

    public function testNewInstance()
    {
        $psc = $this->getMockForFactoryExpectsOnce('ZBateson\MailMimeParser\Message\Factory\PartStreamContainerFactory', 'ZBateson\MailMimeParser\Message\PartStreamContainer');
        $phc = $this->getMockForFactoryExpectsOnce('ZBateson\MailMimeParser\Message\Factory\PartHeaderContainerFactory', 'ZBateson\MailMimeParser\Message\PartHeaderContainer');
        $pcc = $this->getMockForFactoryExpectsOnce('ZBateson\MailMimeParser\Message\Factory\PartChildrenContainerFactory', 'ZBateson\MailMimeParser\Message\PartChildrenContainer');
        
        $sdf = $this->getMockBuilder('ZBateson\MailMimeParser\Stream\StreamFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $sdf->expects($this->once())
            ->method('newMessagePartStream')
            ->with($this->isInstanceOf('\ZBateson\MailMimeParser\Message\MimePart'))
            ->willReturn(Psr7\stream_for('test'));

        $instance = new MimePartFactory($sdf, $psc, $phc, $pcc);
        $part = $instance->newInstance();
        $this->assertInstanceOf(
            '\ZBateson\MailMimeParser\Message\MimePart',
            $part
        );
    }
}
