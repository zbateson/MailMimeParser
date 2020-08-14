<?php
/**
 * This file is part of the ZBateson\MailMimeParser project.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace ZBateson\MailMimeParser\Header;

abstract class Header
{
    const FROM = 'from';
    const TO = 'to';
    const SUBJECT = 'subject';
    const MESSAGE_ID = 'message-id';
    const CONTENT_TYPE = '';
    const CC = 'cc';
    const BCC = 'bcc';
    const DATE = 'date';
}