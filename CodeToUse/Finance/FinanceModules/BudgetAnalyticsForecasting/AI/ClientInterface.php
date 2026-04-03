<?php
namespace Modules\BudgetAnalyticsForecasting\AI;

interface ClientInterface {
  /**
   * Return a forecast given historical monthly amounts.
   * @param array<int,float> $history values only (months inferred by index)
   * @param int $months Number of months to forecast
   * @param array $context Arbitrary metadata (category, project, notes)
   * @return array{forecast: array<int,float>, explanation: string}
   */
  public function forecast(array $history, int $months, array $context = []): array;
}