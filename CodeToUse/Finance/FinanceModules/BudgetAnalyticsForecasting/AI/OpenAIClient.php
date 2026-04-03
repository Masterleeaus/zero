<?php
namespace Modules\BudgetAnalyticsForecasting\AI;

use GuzzleHttp\Client as Http;
use Illuminate\Support\Facades\Config;

class OpenAIClient implements ClientInterface {
  protected Http $http;
  protected string $apiKey;
  protected string $baseUri;
  protected string $model;

  public function __construct(array $cfg) {
    // Prefer config/ai.php (env-backed), never hard-code
    $this->apiKey = $cfg['openai']['api_key'] ?? '';
    $this->baseUri = $cfg['openai']['base_uri'] ?? 'https://api.openai.com/v1';
    $this->model   = $cfg['default_model'] ?? 'gpt-4o-mini';
    $this->http = new Http([ 'base_uri' => $this->baseUri, 'timeout' => 20 ]);
  }

  public function forecast(array $history, int $months, array $context = []): array {
    if (empty($this->apiKey)) {
      $naive = $this->naiveForecast($history, $months);
      return ['forecast' => $naive, 'explanation' => 'Naive forecast: OpenAI key missing (.env or config/ai.php)'];
    }

    $prompt = "Given monthly budget history values, predict the next {$months} months. " .
      "Return ONLY a JSON object: {\"values\":[...],\"explanation\":\"...\"}. " .
      "History values: " . json_encode(array_values($history)) . ". Context: " . json_encode($context) . ".";

    try {
      $res = $this->http->post('/chat/completions', [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->apiKey,
          'Content-Type' => 'application/json'
        ],
        'json' => [
          'model' => $this->model,
          'messages' => [
            ['role' => 'system', 'content' => 'You are an expert financial forecaster. Respond with minimal JSON only.'],
            ['role' => 'user', 'content' => $prompt]
          ],
          'temperature' => 0.2
        ]
      ]);
      $data = json_decode((string)$res->getBody(), true);
      $text = $data['choices'][0]['message']['content'] ?? '{}';
      $parsed = json_decode($text, true);
      if (!is_array($parsed) || !isset($parsed['values'])) {
        $naive = $this->naiveForecast($history, $months);
        return ['forecast' => $naive, 'explanation' => 'Fallback: unexpected AI response format'];
      }
      $vals = array_map('floatval', $parsed['values']);
      if (count($vals) !== $months) {
        $last = count($vals) > 0 ? end($vals) : 0;
        while (count($vals) < $months) { $vals[] = $last; }
        $vals = array_slice($vals, 0, $months);
      }

      return ['forecast' => $vals, 'explanation' => $parsed['explanation'] ?? 'AI forecast'];
    } catch (\Throwable $e) {
      $naive = $this->naiveForecast($history, $months);
      return ['forecast' => $naive, 'explanation' => 'Naive forecast: ' . $e->getMessage()];
    }
  }

  protected function naiveForecast(array $history, int $months): array {
    $n = count($history);
    if ($n < 2) return array_fill(0, $months, $history[0] ?? 0);
    $vals = array_values($history);
    $last = $vals[$n-1];
    $first = $vals[0];
    $slope = ($last - $first) / max(1, $n - 1);
    $out = [];
    for ($i=1; $i<=$months; $i++) {
      $out[] = $last + $slope * $i;
    }
    return $out;
  }
}