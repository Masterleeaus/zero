<?php

namespace App\Services;

use App\Traits\GoogleOAuth;
use Exception;
use Google_Client;

class Google
{

    use GoogleOAuth;

    protected $customer;

    public function __construct()
    {
        $this->setGoogleoAuthConfig();

        $customer = new Google_Client();
        $customer->setClientId(config('services.google.client_id'));
        $customer->setClientSecret(config('services.google.client_secret'));
        $customer->setRedirectUri(config('services.google.redirect_uri'));
        $customer->setScopes(config('services.google.scopes'));
        $customer->setApprovalPrompt(config('services.google.approval_prompt'));
        $customer->setAccessType(config('services.google.access_type'));
        $customer->setIncludeGrantedScopes(config('services.google.include_granted_scopes'));
        $customer->setState(route('googleAuth'));
        $this->customer = $customer;
    }

    public function connectUsing($token)
    {
        $this->customer->setAccessToken($token);

        return $this;
    }

    public function revokeToken($token = null)
    {
        $token = $token ?? $this->customer->getAccessToken();

        return $this->customer->revokeToken($token);
    }

    public function service($service)
    {
        $classname = 'Google_Service_' . $service;

        return new $classname($this->customer);
    }

    public function __call($method, $args)
    {
        if (!method_exists($this->customer, $method)) {
            throw new Exception('Call to undefined method ' . $method);
        }

        return call_user_func_array([$this->customer, $method], $args);
    }

}
