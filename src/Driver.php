<?php

namespace ExtCore\Sms;

/**
 * 短信驱动
 * Class Driver
 * @package ExtCore\Sms
 */
abstract class Driver implements SmsInterface
{
    /**
     * 配置
     * @var array
     */
    protected $options = [];

    /**
     * 构造函数
     * Driver constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /***
     * 返回结果
     * @param $status
     * @param mixed $data
     * @return array
     */
    public static function result($status, $data = null): array
    {
        return [$status, $data];
    }

    /**
     * 设置手机号
     * @param $phone
     * @return static
     */
    public function setPhone($phone): Driver
    {
        $this->options['mobile'] = is_array($phone) ? $phone : array_filter(explode(',', $phone));
        return $this;
    }

    /***
     * 获取配置
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * 设置配置
     * @param $name
     * @param null $value
     * @return static
     */
    public function setOptions($name, $value = null): Driver
    {
        $this->options = array_merge($this->options, is_array($name) ? $name : [$name => $value]);
        return $this;
    }

    /**
     * 设置签名
     * @param $sign
     * @return static
     */
    public function setSign($sign): Driver
    {
        $this->options['sign'] = $sign;
        return $this;
    }

    public function sendBatch(): array
    {
        return [false, '驱动缺少处理事件'];
    }

    public function sendOne(): array
    {
        return [false, '驱动缺少处理事件'];
    }

    /***
     * 推送任务到队列中
     * @param string $string 通道编号
     * @param array $params 通道参数补充
     * @return array
     */
    public function pushJob($string = 'X0', array $params = []): array
    {
        return [true, uniqid(), $string, $this->options];
    }
}
