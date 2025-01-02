<?php

namespace Turnstile\Forms;

use SilverStripe\SpamProtection\SpamProtector;

class TurnstileProtector implements SpamProtector
{
    public function getFormField($name = "TurnstileProtector", $title = "Captcha", $value = null)
    {
        return TurnstileField::create($name, $title);
    }

    public function setFieldMapping($fieldMapping){}
}