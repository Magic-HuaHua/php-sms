<?php

namespace ExtCore\Sms\Driver;

use ExtCore\Sms\{Driver, SmsException};
use Qcloud\Sms\SmsSingleSender;

/**
 * 腾讯云短信验证码
 * Class QcloudSms
 * @package ExtCore\Sms\Driver
 */
class QcloudSms extends Driver
{
    protected $options = [
        'appid' => '',
        'appkey' => '',
        'sign' => '',
        'tid' => '',
        'nationCode' => 86,
        'mobile' => []
    ];

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        # 检查环境
        if (!class_exists('Qcloud\Sms\SmsSingleSender')) {
            throw new SmsException('缺少 qcloudsms/qcloudsms_php 环境支持', ['解决方案 | console: composer require qcloudsms/qcloudsms_php', '项目地址 | https://github.com/qcloudsms/qcloudsms_php']);
        }
    }

    public function sendOne(): array
    {
        # 参数检查
        if (!is_array($this->options['mobile']) || empty($this->options['mobile'][0])) {
            return self::result(false, '手机号不正确');
        }
        $appid = $this->options['appid'];
        $appkey = $this->options['appkey'];
        # 创建Sender对象
        $result = (new SmsSingleSender($appid, $appkey))->sendWithParam(
            $this->options['nationCode'],
            $this->options['mobile'][0],
            $this->options['tid'],
            $this->options['params'],
            $this->options['sign'],
            $this->options['extend'] ?? '',
            $this->options['ext'] ?? ''
        );
        # 得到发送结果
        $rsp = json_decode($result, true);
        if (isset($rsp['result']) === false) {
            return self::result(false, $result);
        }
        if ($rsp['result'] !== 0) {
            return self::result(false, $rsp['errmsg']);
        }

        return self::result(true, $rsp);
    }

    /**
     * 设置模板
     * @param $tplCode
     * @param mixed $tplParam
     * @return Driver
     */
    public function setTemplate($tplCode, $tplParam = null): Driver
    {
        $this->options['tid'] = $tplCode;
        $this->options['params'] = $tplParam;
        return $this;
    }

    /**
     * 设置收信手机号
     * @param $phone
     * @return Driver
     */
    public function setPhone($phone): Driver
    {
        return parent::setPhone($phone);
    }
}