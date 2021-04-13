<?php
namespace ExtCore\Sms;

/**
 * 短信接口
 * Interface SmsInterface
 * @package ExtCore\Sms
 */
interface SmsInterface
{
    /**
     * 设置签名
     * @param $sign
     * @return Driver
     */
    public function setSign($sign): Driver;
    /**
     * 设置模板
     * @param $tplCode
     * @param mixed $tplParam
     * @return Driver
     */
    public function setTemplate($tplCode, $tplParam = null): Driver;
    /**
     * 设置手机号
     * @param $phone
     * @return Driver
     */
    public function setPhone($phone): Driver;
    /**
     * 单个发送
     * @return array
     */
    public function sendOne(): array;
    /**
     * 批量发送
     * @return array
     */
    public function sendBatch(): array;
}
