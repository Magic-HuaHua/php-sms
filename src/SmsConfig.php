<?php

namespace ExtCore\Sms;

/**
 * 短信配置类
 * Class SmsConfig
 * @package ExtCore\Sms
 */
class SmsConfig
{
    /**
     * 驱动名称
     * @var string
     */
    protected $driver = '';
    /**
     * 配置参数
     * @var array
     */
    protected $options = [];

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function setDriver($driver): SmsConfig
    {
        $this->driver = $driver;
        return $this;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}
