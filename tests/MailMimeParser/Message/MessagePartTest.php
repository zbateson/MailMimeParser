<?php

namespace ZBateson\MailMimeParser\Message;

use LegacyPHPUnit\TestCase;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\StreamWrapper;
use org\bovigo\vfs\vfsStream;
use Exception;

/**
 * MessagePartFactoryTest
 *
 * @group MessagePartClass
 * @group MessagePart
 * @covers ZBateson\MailMimeParser\Message\MessagePart
 * @author Zaahid Bateson
 */
class MessagePartTest extends TestCase {

    protected $partStreamContainer;
    private $vfs;

    protected function legacySetUp()
    {
        $this->vfs = vfsStream::setup('root');
        $this->partStreamContainer = $this->getMockBuilder('ZBateson\MailMimeParser\Message\PartStreamContainer')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMessagePart($handle = 'habibi', $contentHandle = null, $parent = null)
    {
        if ($contentHandle !== null) {
            $contentHandle = Psr7\stream_for($contentHandle);
            $this->partStreamContainer
                ->method('getContentStream')
                ->willReturnCallback(function() use ($contentHandle) {
                    try {
                        $contentHandle->rewind();
                    } catch (Exception $e) {

                    }
                    return $contentHandle;
                });
        }
        if ($handle !== null) {
            $handle = Psr7\stream_for($handle);
            $this->partStreamContainer
                ->method('getStream')
                ->willReturnCallback(function() use ($handle) {
                    try {
                        $handle->rewind();
                    } catch (Exception $e) {

                    }
                    return $handle;
                });
        }
        return $this->getMockForAbstractClass(
            'ZBateson\MailMimeParser\Message\MessagePart',
            [ $this->partStreamContainer, $parent ]
        );
    }

    public function testPartStreamHandle()
    {
        $messagePart = $this->getMessagePart();
        $this->assertNotNull($messagePart);

        $this->partStreamContainer->expects($this->atLeastOnce())->method('hasContent')->willReturn(false);
        $this->assertFalse($messagePart->hasContent());

        $this->assertNull($messagePart->getContentStream());
        $this->assertNull($messagePart->getContent());
        $this->assertNull($messagePart->getParent());
        $this->assertEquals('habibi', stream_get_contents($messagePart->getResourceHandle()));
        $this->assertEquals('habibi', $messagePart->getStream()->getContents());
    }

    public function testContentStreamAndCharsetOverride()
    {
        $messagePart = $this->getMessagePart('Que tonta', 'Que tonto');
        $messagePart->method('getContentTransferEncoding')
            ->willReturn('wubalubadub-duuuuub');
        $messagePart->method('getCharset')
            ->willReturn('wigidiwamwamwazzle');

        $this->partStreamContainer->method('hasContent')->willReturn(true);
        $this->partStreamContainer->expects($this->exactly(2))
            ->method('getContentStream')
            ->withConsecutive(
                ['wubalubadub-duuuuub', 'wigidiwamwamwazzle', 'oooohweee!'],
                ['wubalubadub-duuuuub', 'override', 'oooohweee!']
            )
            ->willReturn('Que tonto');

        $this->assertEquals('Que tonto', $messagePart->getContentStream('oooohweee!'));
        $messagePart->setCharsetOverride('override');
        $this->assertEquals('Que tonto', $messagePart->getContentStream('oooohweee!'));
    }

    public function testBinaryContentStream()
    {
        $f = Psr7\stream_for('First');
        $s = Psr7\stream_for('Second');

        $messagePart = $this->getMessagePart('Que tonta', 'Setup');
        $messagePart->method('getContentTransferEncoding')
            ->willReturn('wubalubadub-duuuuub');
        
        $this->partStreamContainer->method('hasContent')->willReturn(true);
        $this->partStreamContainer
            ->expects($this->never())
            ->method('getContentStream');
        $this->partStreamContainer
            ->expects($this->exactly(2))
            ->method('getBinaryContentStream')
            ->willReturnOnConsecutiveCalls($f, $s);

        $this->assertEquals('First', $messagePart->getBinaryContentStream()->getContents());
        $this->assertEquals('Second', stream_get_contents($messagePart->getBinaryContentResourceHandle()));
    }

    public function testSaveContent()
    {
        $messagePart = $this->getMessagePart('Que tonta', 'Setup');
        $messagePart->method('getContentTransferEncoding')
            ->willReturn('wubalubadub-duuuuub');
        $f = Psr7\stream_for('Que tonto');
        $s = Psr7\stream_for('Que tonto');

        $this->partStreamContainer->method('hasContent')->willReturn(true);
        $this->partStreamContainer
            ->expects($this->never())
            ->method('getContentStream');
        $this->partStreamContainer
            ->expects($this->once())
            ->method('getBinaryContentStream')
            ->willReturnOnConsecutiveCalls($f, $s);

        $content = vfsStream::newFile('part')->at($this->vfs);
        $messagePart->saveContent($content->url());
        $this->assertEquals('Que tonto', file_get_contents($content->url()));
    }

    public function testSaveContentToStream()
    {
        $messagePart = $this->getMessagePart('Que tonta', 'Setup');
        $messagePart->method('getContentTransferEncoding')
            ->willReturn('wubalubadub-duuuuub');
        $f = Psr7\stream_for('Que tonto');
        $s = Psr7\stream_for('Que tonto');
        
        $this->partStreamContainer->method('hasContent')->willReturn(true);
        $this->partStreamContainer
            ->expects($this->never())
            ->method('getContentStream');
        $this->partStreamContainer
            ->expects($this->once())
            ->method('getBinaryContentStream')
            ->willReturnOnConsecutiveCalls($f, $s);

        $stream = Psr7\stream_for();
        $messagePart->saveContent($stream);
        $stream->rewind();

        $this->assertEquals('Que tonto', $stream->getContents());
    }

    public function testSaveContentToResource()
    {
        $messagePart = $this->getMessagePart('Que tonta', 'Setup');
        $messagePart->method('getContentTransferEncoding')
            ->willReturn('wubalubadub-duuuuub');
        $f = Psr7\stream_for('Que tonto');
        $s = Psr7\stream_for('Que tonto');

        $this->partStreamContainer->method('hasContent')->willReturn(true);
        $this->partStreamContainer
            ->expects($this->never())
            ->method('getContentStream');
        $this->partStreamContainer
            ->expects($this->once())
            ->method('getBinaryContentStream')
            ->willReturnOnConsecutiveCalls($f, $s);

        $res = StreamWrapper::getResource(Psr7\stream_for());
        $messagePart->saveContent($res);
        rewind($res);

        $this->assertEquals('Que tonto', stream_get_contents($res));
        fclose($res);
    }

    public function testDetachContentStream()
    {
        $stream = Psr7\stream_for('Que tonta');
        $contentStream = Psr7\stream_for('Que tonto');
        $messagePart = $this->getMessagePart($stream, $contentStream);

        $this->partStreamContainer
            ->expects($this->once())
            ->method('setContentStream')
            ->with(null);

        $observer = $this->getMockForAbstractClass('SplObserver');
        $observer->expects($this->once())
            ->method('update');
        $messagePart->attach($observer);

        $messagePart->detachContentStream();
    }

    public function testNotify()
    {
        $messagePart = $this->getMessagePart();
        $observer = $this->getMockForAbstractClass('SplObserver');
        $observer->expects($this->once())
            ->method('update');
        $messagePart->attach($observer);
        $messagePart->notify();
        $messagePart->detach($observer);
        $messagePart->notify();
    }

    public function testGetFilenameReturnsNull()
    {
        $messagePart = $this->getMessagePart();
        $this->assertNull($messagePart->getFilename());
    }

    public function testGetContent()
    {
        $messagePart = $this->getMessagePart('habibi', 'sopa di agua con rocas');
        $this->partStreamContainer->method('hasContent')->willReturn(true);
        $this->assertEquals('sopa di agua con rocas', $messagePart->getContent());
    }

    public function testSaveAndToString()
    {
        $messagePart = $this->getMessagePart(
            'Demigorgon',
            Psr7\stream_for('other demons')
        );

        $handle = fopen('php://temp', 'r+');
        $messagePart->save($handle);
        rewind($handle);
        $str = stream_get_contents($handle);
        fclose($handle);

        $this->assertEquals('Demigorgon', $str);
        $this->assertEquals('Demigorgon', $messagePart->__toString());
    }

    public function testSaveToFile()
    {
        $messagePart = $this->getMessagePart(
            'Demigorgon',
            Psr7\stream_for('other demons')
        );

        $part = vfsStream::newFile('part')->at($this->vfs);
        $messagePart->save($part->url());
        $this->assertEquals('Demigorgon', file_get_contents($part->url()));
    }

    public function testSetContentAndAttachContentStream()
    {
        $ms = Psr7\stream_for('message');
        $org = Psr7\stream_for('content');
        $messagePart = $this->getMessagePart($ms, $org);
        $messagePart->method('getContentTransferEncoding')
            ->willReturn('quoted-printable');
        $messagePart->method('getCharset')
            ->willReturn('utf-64');

        $new = Psr7\stream_for('updated');
        $this->partStreamContainer->method('hasContent')->willReturn(true);
        $this->partStreamContainer
            ->method('getContentStream')
            ->withConsecutive(
                ['', 'charset', 'a-charset']
        );

        $this->assertSame($ms, $messagePart->getStream());

        $this->partStreamContainer
            ->method('setContentStream')
            ->with($new);

        $observer = $this->getMockForAbstractClass('SplObserver');
        $observer->expects($this->once())
            ->method('update');
        $messagePart->attach($observer);

        $messagePart->setContent($new, 'charset');

        // actually returns $org because of method definition in getMessagePart
        $messagePart->getContentStream('a-charset');
    }

    public function testParentAndParentNotify()
    {
        $parent = $this->getMockBuilder('ZBateson\MailMimeParser\Message\MimePart')
            ->disableOriginalConstructor()
            ->getMock();
        $messagePart = $this->getMessagePart('blah', 'blooh', $parent);

        $this->assertSame($parent, $messagePart->getParent());
        $parent->expects($this->once())->method('notify');
        $messagePart->notify();
    }
}
