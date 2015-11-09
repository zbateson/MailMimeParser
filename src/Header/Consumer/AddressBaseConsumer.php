<?php
namespace ZBateson\MailMimeParser\Header\Consumer;

use Iterator;

/**
 * Serves as a base-consumer for recipient/sender email address headers (like
 * From and To).
 * 
 * AddressBaseConsumer passes on token processing to its sub-consumer, an
 * AddressConsumer, and collects Part\Address objects processed and returned by
 * AddressConsumer.
 *
 * @author Zaahid Bateson
 */
class AddressBaseConsumer extends AbstractConsumer
{
    /**
     * Returns \ZBateson\MailMimeParser\Header\Consumer\AddressConsumer as a
     * sub-consumer.
     * 
     * @return AbstractConsumer[] the sub-consumers
     */
    protected function getSubConsumers()
    {
        return [
            $this->consumerService->getAddressConsumer()
        ];
    }
    
    /**
     * Returns an empty array.
     * 
     * @return string[] an array of regex pattern matchers
     */
    protected function getTokenSeparators()
    {
        return [];
    }
    
    /**
     * Disables advancing for start tokens.
     * 
     * The start token for AddressBaseConsumer is part of an Address (or a
     * sub-consumer) and so must be passed on.
     * 
     * @param Iterator $tokens
     * @param bool $isStartToken
     */
    protected function advanceToNextToken(Iterator $tokens, $isStartToken)
    {
        if ($isStartToken) {
            return;
        }
        parent::advanceToNextToken($tokens, $isStartToken);
    }
    
    /**
     * AddressBaseConsumer doesn't have start/end tokens, and so always returns
     * false.
     * 
     * @param string $token
     * @return boolean false
     */
    protected function isEndToken($token)
    {
        return false;
    }
    
    /**
     * AddressBaseConsumer doesn't have start/end tokens, and so always returns
     * false.
     * 
     * @param string $token
     * @return boolean false
     */
    protected function isStartToken($token)
    {
        return false;
    }
    
    /**
     * Never reached by AddressBaseConsumer. Overridden to satisfy
     * AbstractConsumer.
     * 
     * @param string $token the token
     * @param bool $isLiteral set to true if the token represents a literal -
     *        e.g. an escaped token
     * @return \ZBateson\MailMimeParser\Header\Part\Part the constructed header
     *         part or null if the token should be ignored
     */
    protected function getPartForToken($token, $isLiteral)
    {
        return $this->partFactory->newToken($token);
    }
}
