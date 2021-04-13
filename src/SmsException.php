<?php

namespace ExtCore\Sms;

use Exception;

/**
 * 短信异常
 * Class SmsException
 * @package ExtCore\Sms
 */
class SmsException extends Exception
{
    protected $tips;

    public function __construct($message = "", array $tips = [])
    {
        $this->tips = $tips;
        parent::__construct($message);
    }

    public function getTips(): array
    {
        return $this->tips;
    }
}