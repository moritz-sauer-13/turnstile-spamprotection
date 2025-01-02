<?php

namespace Turnstile\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\View\Requirements;

class TurnstileField extends FormField
{
    private static $site_key;

    private static $secret_key;

    private static $cf_js_url = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

    private static $cf_verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Available sizes are "flexible", "normal" and "compact"
     */
    private static $size = 'normal';

    /**
     * Available themes are "auto", "light" and "dark"
     */
    private static $theme = 'light';

    private static $verify_ssl = true;

    public function __construct($name, $title = null, $value = null)
    {
        parent::__construct($name, $title, $value);
        $this->title = $title;
    }

    public function Field($properties = array())
    {
        $siteKey = $this->getSiteKey();
        $secretKey = $this->getSecretKey();

        if (!$siteKey || !$secretKey) {
            user_error('You must configure a site key and secret key for TurnstileField');
        }

        Requirements::javascript(self::config()->cf_js_url);

        return parent::Field($properties);
    }

    public function getSiteKey()
    {
        return self::config()->site_key;
    }

    public function getSecretKey()
    {
        return self::config()->secret_key;
    }

    public function getSize()
    {
        return self::config()->size;
    }

    public function getTheme()
    {
        return self::config()->theme;
    }

    public function getVerifySSL()
    {
        return self::config()->verify_ssl;
    }

    public function validate($validator)
    {
        $captchaResponse = Controller::curr()->getRequest()->requestVar('cf-turnstile-response');

        if(!isset($captchaResponse)) {
            $validator->validationError($this->name, _t(TurnstileField::class . '.EMPTY', '_Please answer the captcha, if you do not see the captcha you must enable JavaScript'), ValidationResult::TYPE_ERROR);
            return false;
        }

        if(!function_exists('curl_init')) {
            user_error('You must enable php-curl to use this field', E_USER_ERROR);
            return false;
        }

        $secretKey = $this->getSecretKey();
        $url = self::config()->cf_verify_url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::getVerifySSL());

        $data = [
            'secret' => $secretKey,
            'response' => $captchaResponse
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if ($response === false) {
            user_error('cURL Error: ' . curl_error($ch), E_USER_ERROR);
        } else {
            $response = json_decode($response, true);
            if ($response['success'] === false) {
                $validator->validationError($this->name, _t(TurnstileField::class . '.WRONG', '_The captcha was wrong, please try again'), ValidationResult::TYPE_ERROR);
                return false;
            }
        }
        return true;
    }
}