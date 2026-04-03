<?php

namespace Modules\Inspection\SDK\PHP;

class InspectionClient
{
    protected string $baseUrl;
    protected string $token;

    public function __construct(string $baseUrl, string $token)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
    }

    protected function req(string $method, string $path, array $data = [])
    {
        $url = $this->baseUrl . '/api/inspection' . $path;
        $opts = [
            'http' => [
                'method' => strtoupper($method),
                'header' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $this->token,
                ],
                'content' => in_array(strtoupper($method), ['POST','PUT','PATCH']) ? json_encode($data) : null,
                'ignore_errors' => true,
            ],
        ];
        $ctx = stream_context_create($opts);
        $res = file_get_contents($url, false, $ctx);
        if ($res === false) {
            $e = error_get_last();
            throw new \RuntimeException('HTTP request failed: ' . ($e['message'] ?? 'unknown'));
        }
        return json_decode($res, true);
    }

    // Schedules
    public function listSchedules() { return $this->req('GET', '/schedules'); }
    public function getSchedule($id) { return $this->req('GET', '/schedules/' . $id); }
    public function createSchedule(array $data) { return $this->req('POST', '/schedules', $data); }
    public function updateSchedule($id, array $data) { return $this->req('PUT', '/schedules/' . $id, $data); }
    public function deleteSchedule($id) { return $this->req('DELETE', '/schedules/' . $id); }
}
