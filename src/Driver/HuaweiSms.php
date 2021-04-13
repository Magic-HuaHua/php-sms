<?php
namespace ExtCore\Sms\Driver;

use ExtCore\Sms\Driver;
use ExtCore\Sms\SmsException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HuaweiSms extends Driver
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        # 检查环境
        if (!class_exists('GuzzleHttp\Client')) {
            throw new SmsException('缺少 guzzlehttp/guzzle 环境支持', ['解决方案 | console: composer require guzzlehttp/guzzle:~6.0', '项目地址 | https://github.com/guzzlehttp/guzzle']);
        }
    }
    protected $options = [
        'app_key'   => '',
        'app_secret'=> '',
        'sign'      => '',
        'sender'    => '',
        'template_id'=> '',
        'template_params'=> ''
    ];
    /**
     * 构造X-WSSE参数值
     * @param string $appKey
     * @param string $appSecret
     * @return string
     */
    function buildWsseHeader(string $appKey, string $appSecret)
    {
        $now = date('Y-m-d\TH:i:s\Z'); //Created
        $nonce = uniqid(); //Nonce
        $base64 = base64_encode(hash('sha256', ($nonce . $now . $appSecret))); //PasswordDigest
        return sprintf("UsernameToken Username=\"%s\",PasswordDigest=\"%s\",Nonce=\"%s\",Created=\"%s\"",
            $appKey, $base64, $nonce, $now);
    }

    /**
     * @param $tplCode
     * @param mixed $tplParam
     * @return HuaweiSms
     */
    public function setTemplate($tplCode, $tplParam = null) : Driver
    {
        $this->options['template_id'] = $tplCode;
        $this->options['template_params'] = $tplParam;
        return $this;
    }

    /**
     * @return array
     */
    public function sendOne() : array
    {
        $phone = $this->options['mobile'];
        //必填,全局号码格式(包含国家码),示例:+8615123456789,多个号码之间用英文逗号分隔
        //$receiver = '+8615123456789,+8615234567890'; //短信接收人号码
        if (empty($phone)) {
            return [false,'手机号不能为空'];
        }
        $receiver = array_map(function ($phone) {
            return strlen($phone) === 11 ? '+86' . $phone : $phone;
        }, is_array($phone) ? $phone : [$phone]);
        $receiver = implode(',', $receiver);

        $template_id     =  $this->options['template_id'];
        $template_params = $this->options['template_params'] ?? '';
        $signature = $this->options['sign'];
        $sender = $this->options['sender'];
        // 选填,短信状态报告接收地址,推荐使用域名,为空或者不填表示不接收状态报告
        $statusCallback = $this->options['status_callback'] ?? '';
        /**
         * 选填,使用无变量模板时请赋空值 $TEMPLATE_PARAS = '';
         * 单变量模板示例:模板内容为"您的验证码是${1}"时,$TEMPLATE_PARAS可填写为 '["369751"]'
         * 双变量模板示例:模板内容为"您有${1}件快递请到${2}领取"时,$TEMPLATE_PARAS可填写为'["3","人民公园正门"]'
         * 模板中的每个变量都必须赋值，且取值不能为空
         * 查看更多模板格式规范:产品介绍>模板和变量规范
         * @var string $TEMPLATE_PARAS
         */
        // 模板变量格式 ["369751"]
        if($template_params){
            if(is_array($template_params)){
                $TEMPLATE_PARAS = '[';
                array_map(function ($value) use (&$TEMPLATE_PARAS) {
                    return $TEMPLATE_PARAS .= '"' . $value . '",';
                }, $template_params);
                $template_params = substr($TEMPLATE_PARAS, 0, strlen($TEMPLATE_PARAS) - 1) . ']';
            }
        }
        //2.执行操作
        $client = new Client();
        try {
            $form_params = [
                'from' => $sender,
                'to' => $receiver,
                'templateId' => $template_id,
                'templateParas' => $template_params,
                'statusCallback' => $statusCallback
            ];
            if (is_string($signature)) {
                $form_params['signature'] = $signature; //使用国内短信通用模板时,必须填写签名名称
            }
            $response = $client->request('POST', 'https://rtcsms.cn-north-1.myhuaweicloud.com:10743/sms/batchSendSms/v1', [
                'form_params' => $form_params,
                'headers' => [
                    'Authorization' => 'WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
                    'X-WSSE' => $this->buildWsseHeader($this->options['app_key'], $this->options['app_secret'])
                ],
                'verify' => false //为防止因HTTPS证书认证失败造成API调用失败，需要先忽略证书信任问题
            ]);
            $result = $response->getBody()->getContents();
            $result = json_decode($result,true);
            $result['status']  = true;
            return [true, $result];
        } catch (RequestException $e) {
            return [false, $e->getResponse()->getBody()->getContents()];
        }
    }
}
