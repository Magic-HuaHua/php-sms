<?php
namespace ExtCore\Sms;

use Exception;

class Sms
{
    private static $namespace = '\\ExtCore\\Sms\\Driver\\';

    private static $defaultSmsDriver = 'qcloud_sms';

    public static function getDefaultDriver() : string
    {
        return self::$defaultSmsDriver;
    }

    public static function setDefaultDriver($driver)
    {
        self::$defaultSmsDriver = $driver;
    }

    private static $isDebugMode = false;

    public static function setDebugMode($open = true){
        self::$isDebugMode = $open;
    }

    /**
     * 连接或者切换缓存
     * @access public
     * @param string|array|SmsConfig $name 连接配置名
     * @param array $options 配置
     * @return object|Driver
     * @throws Exception
     */
    public static function store($name = null, array $options = []): Driver
    {
        if($name instanceof SmsConfig){
            $options = array_merge($name->getOptions(), $options);
            $name = $name->getDriver();
        }

        if(empty($name)){
            $name = self::getDefaultDriver();
        }

        $name = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
        $class = self::$namespace.$name;
        if(!class_exists($class)){
            throw new SmsException("短信驱动`{$name}`不存在");
        }
        try{
            return new $class($options);
        }catch(Exception $e){
            throw new SmsException($e->getMessage());
        }
    }
}

# 互亿无线
// Sms::store('ihuyi_sms')->setPhone(17623031599)->setOptions(['APIID' => 'C59305163','APIKEY'=> '44bca5accc5bbaead259ff67dd72d215'])->setTemplate('您的验证码是：{code}。请不要把验证码泄露给其他人。', ['code'=>rand(1000,9999)])->pushJob()
# 华为云
// Sms::store('huawei_sms', ['app_key'=>'TO42uToxe3h17Y8R7oMH8flxpjq5','app_secret'=>'65o77N6v4nbesC43Sd53O3SnivNu','sender'=>'10690400999304230'])->setSign('权行股份')->setPhone(17623031599)->setTemplate('a216853acc3a4ed19ce8040df23728b5', ['code'=>rand(1000,9999)])->sendOne()
# 腾讯云
// Sms::store(get_sms_config())->setTemplate('464954',[$code,5])->setPhone($phone)->sendOne();
