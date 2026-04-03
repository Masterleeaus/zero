<?php

namespace Modules\TitanHello\Services\TwiML;

class TwiMLBuilder
{
    /** @var array<int, string> */
    protected array $lines = [];

    public function __construct()
    {
        $this->lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $this->lines[] = '<Response>';
    }

    public function say(string $text, string $voice = 'alice'): self
    {
        $safe = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $this->lines[] = "<Say voice=\"{$voice}\">{$safe}</Say>";
        return $this;
    }

    public function pause(int $length = 1): self
    {
        $length = max(1, min(10, $length));
        $this->lines[] = "<Pause length=\"{$length}\" />";
        return $this;
    }

    public function hangup(): self
    {
        $this->lines[] = "<Hangup />";
        return $this;
    }

    public function redirect(string $url, string $method = 'POST'): self
    {
        $safe = htmlspecialchars($url, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $method = strtoupper($method) === 'GET' ? 'GET' : 'POST';
        $this->lines[] = "<Redirect method=\"{$method}\">{$safe}</Redirect>";
        return $this;
    }

    
public function record(string $actionUrl, array $options = []): self
{
    $action = htmlspecialchars($actionUrl, ENT_QUOTES | ENT_XML1, 'UTF-8');
    $method = strtoupper((string)($options['method'] ?? 'POST'));
    if (!in_array($method, ['GET','POST'], true)) { $method = 'POST'; }

    $attrs = [
        'action' => $action,
        'method' => $method,
    ];

    // Optional attributes supported by Twilio <Record>
    $optMap = [
        'timeout' => 'timeout',
        'maxLength' => 'maxLength',
        'playBeep' => 'playBeep',
        'trim' => 'trim',
        'recordingStatusCallback' => 'recordingStatusCallback',
        'recordingStatusCallbackMethod' => 'recordingStatusCallbackMethod',
    ];

    foreach ($optMap as $k => $attr) {
        if (array_key_exists($k, $options) && $options[$k] !== null && $options[$k] !== '') {
            $v = htmlspecialchars((string)$options[$k], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $attrs[$attr] = $v;
        }
    }

    $attrStr = '';
    foreach ($attrs as $k => $v) {
        $attrStr .= " {$k}="{$v}"";
    }

    $this->lines[] = "<Record{$attrStr} />";
    return $this;
}

public function dial(array $numbers, int $timeoutSeconds = 25): self
    {
        $timeoutSeconds = max(5, min(60, $timeoutSeconds));
        $this->lines[] = "<Dial timeout=\"{$timeoutSeconds}\">";
        foreach ($numbers as $n) {
            $safe = htmlspecialchars($n, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $this->lines[] = "<Number>{$safe}</Number>";
        }
        $this->lines[] = "</Dial>";
        return $this;
    }

    public function gather(string $actionUrl, int $timeoutSeconds = 6, int $numDigits = 1): self
    {
        $safe = htmlspecialchars($actionUrl, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $timeoutSeconds = max(3, min(20, $timeoutSeconds));
        $numDigits = max(1, min(5, $numDigits));
        $this->lines[] = "<Gather action=\"{$safe}\" method=\"POST\" timeout=\"{$timeoutSeconds}\" numDigits=\"{$numDigits}\">";
        return $this;
    }

    public function endGather(): self
    {
        $this->lines[] = "</Gather>";
        return $this;
    }

    public function build(): string
    {
        $this->lines[] = '</Response>';
        return implode("\n", $this->lines);
    }
}
