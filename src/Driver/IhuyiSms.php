<?php

namespace ExtCore\Sms\Driver;

use ExtCore\Sms\Driver;

class IhuyiSms extends Driver
{
    protected $options = [
        'APIID' => '',
        'APIKEY' => '',
        'template' => [],
        'content' => '',
        'mobile' => [],
        'param' => [],
        'target' => 'http://106.ihuyi.com/webservice/sms.php?method=Submit'
    ];

    public function sendOne(): array
    {
        # 参数检查
        if (!is_array($this->options['mobile']) || empty($this->options['mobile'][0])) {
            return self::result(false, '手机号不正确');
        }
        $mobile = $this->options['mobile'][0];

        $post_data = "account={$this->options['APIID']}&password={$this->options['APIKEY']}&mobile={$mobile}&content={$this->options['content']}";

        $gets = $this->request($post_data);
        if ($gets['SubmitResult']['code'] == 2) {
            return self::result(true, [
                'smsid' => $gets['SubmitResult']['smsid'],
                'mobile' => $mobile,
                'param' => $this->options['param']
            ]);
        }
        return self::result(false, $gets['SubmitResult']['msg']);
    }

    /**
     * @param $tplCode
     * @param null $tplParam
     * @return $this|Driver
     */
    public function setTemplate($tplCode, $tplParam = null): Driver
    {
        $this->setOptions(['tpl' => (string)$tplCode, 'param' => (array)$tplParam]);
        $tpl = $this->options['tpl'];
        foreach ($this->options['param'] as $f => $v) {
            $tpl = str_replace("{{$f}}", $v, $tpl);
        }
        $this->options['content'] = $tpl;
        return $this;
    }

    private function xml_to_array($xml)
    {
        $arr = [];
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    private function request($post_data): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->options['target']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $this->xml_to_array($return_str);
    }
}
