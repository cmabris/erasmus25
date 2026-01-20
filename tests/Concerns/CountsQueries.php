<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Trait for counting database queries in tests.
 *
 * Usage in tests:
 *
 * ```php
 * it('loads calls index with optimal queries', function () {
 *     $this->startQueryLog();
 *
 *     // Perform actions...
 *     Livewire::test(Index::class);
 *
 *     $this->assertQueryCountLessThan(15);
 *     // Or check for duplicates:
 *     $this->assertNoDuplicateQueries();
 * });
 * ```
 */
trait CountsQueries
{
    /**
     * Logged queries during the test.
     *
     * @var array<int, array{query: string, bindings: array, time: float}>
     */
    protected array $queryLog = [];

    /**
     * Start logging database queries.
     */
    protected function startQueryLog(): void
    {
        $this->queryLog = [];
        DB::flushQueryLog();
        DB::enableQueryLog();
    }

    /**
     * Stop logging and get the query log.
     *
     * @return array<int, array{query: string, bindings: array, time: float}>
     */
    protected function stopQueryLog(): array
    {
        $this->queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        return $this->queryLog;
    }

    /**
     * Get the current query count.
     */
    protected function getQueryCount(): int
    {
        if (empty($this->queryLog)) {
            $this->queryLog = DB::getQueryLog();
        }

        return count($this->queryLog);
    }

    /**
     * Get all logged queries.
     *
     * @return array<int, array{query: string, bindings: array, time: float}>
     */
    protected function getQueries(): array
    {
        if (empty($this->queryLog)) {
            $this->queryLog = DB::getQueryLog();
        }

        return $this->queryLog;
    }

    /**
     * Get total query time in milliseconds.
     */
    protected function getTotalQueryTime(): float
    {
        return collect($this->getQueries())->sum('time');
    }

    /**
     * Get queries that exceed a time threshold.
     *
     * @param  float  $threshold  Time in milliseconds
     * @return array<int, array{query: string, bindings: array, time: float}>
     */
    protected function getSlowQueries(float $threshold = 100.0): array
    {
        return collect($this->getQueries())
            ->filter(fn ($query) => $query['time'] > $threshold)
            ->values()
            ->all();
    }

    /**
     * Get duplicate queries (potential N+1).
     *
     * @return array<string, int> Query pattern => count
     */
    protected function getDuplicateQueries(): array
    {
        $patterns = collect($this->getQueries())
            ->map(fn ($query) => $this->normalizeQuery($query['query']))
            ->countBy()
            ->filter(fn ($count) => $count > 1)
            ->all();

        return $patterns;
    }

    /**
     * Normalize a query for comparison (replace specific values with placeholders).
     */
    protected function normalizeQuery(string $query): string
    {
        // Replace numeric values
        $query = preg_replace('/\b\d+\b/', '?', $query);

        // Replace quoted strings
        $query = preg_replace("/'[^']*'/", '?', $query);

        // Replace IN clauses with multiple values
        $query = preg_replace('/\bIN\s*\(\s*\?(?:\s*,\s*\?)*\s*\)/i', 'IN (?)', $query);

        return $query;
    }

    /**
     * Assert that query count is less than a threshold.
     */
    protected function assertQueryCountLessThan(int $maxQueries, ?string $message = null): void
    {
        $count = $this->getQueryCount();
        $message = $message ?? "Expected less than {$maxQueries} queries, but {$count} were executed.";

        if ($count >= $maxQueries) {
            $this->outputQueryDetails();
        }

        $this->assertLessThan($maxQueries, $count, $message);
    }

    /**
     * Assert that query count equals expected.
     */
    protected function assertQueryCount(int $expectedQueries, ?string $message = null): void
    {
        $count = $this->getQueryCount();
        $message = $message ?? "Expected exactly {$expectedQueries} queries, but {$count} were executed.";

        if ($count !== $expectedQueries) {
            $this->outputQueryDetails();
        }

        $this->assertEquals($expectedQueries, $count, $message);
    }

    /**
     * Assert there are no duplicate queries (N+1 detection).
     *
     * @param  array<string>  $allowedPatterns  Patterns that are allowed to be duplicated
     */
    protected function assertNoDuplicateQueries(array $allowedPatterns = [], ?string $message = null): void
    {
        $duplicates = $this->getDuplicateQueries();

        // Filter out allowed patterns
        foreach ($allowedPatterns as $pattern) {
            $duplicates = array_filter(
                $duplicates,
                fn ($query) => ! str_contains($query, $pattern),
                ARRAY_FILTER_USE_KEY
            );
        }

        if (! empty($duplicates)) {
            $message = $message ?? 'Potential N+1 queries detected:';
            $details = collect($duplicates)
                ->map(fn ($count, $query) => "  - {$query} (executed {$count} times)")
                ->implode("\n");

            $this->fail("{$message}\n{$details}");
        }

        $this->assertTrue(true);
    }

    /**
     * Assert no queries exceed the time threshold.
     *
     * @param  float  $threshold  Time in milliseconds
     */
    protected function assertNoSlowQueries(float $threshold = 100.0, ?string $message = null): void
    {
        $slowQueries = $this->getSlowQueries($threshold);

        if (! empty($slowQueries)) {
            $message = $message ?? "Slow queries detected (>{$threshold}ms):";
            $details = collect($slowQueries)
                ->map(fn ($query) => "  - {$query['time']}ms: {$query['query']}")
                ->implode("\n");

            $this->fail("{$message}\n{$details}");
        }

        $this->assertTrue(true);
    }

    /**
     * Assert total query time is less than threshold.
     *
     * @param  float  $maxTime  Time in milliseconds
     */
    protected function assertTotalQueryTimeLessThan(float $maxTime, ?string $message = null): void
    {
        $totalTime = $this->getTotalQueryTime();
        $message = $message ?? "Expected total query time less than {$maxTime}ms, but took {$totalTime}ms.";

        $this->assertLessThan($maxTime, $totalTime, $message);
    }

    /**
     * Output query details for debugging.
     */
    protected function outputQueryDetails(): void
    {
        $queries = $this->getQueries();

        echo "\n=== Query Log ({$this->getQueryCount()} queries, {$this->getTotalQueryTime()}ms total) ===\n";

        foreach ($queries as $index => $query) {
            $num = $index + 1;
            echo "[{$num}] {$query['time']}ms: {$query['query']}\n";
        }

        $duplicates = $this->getDuplicateQueries();
        if (! empty($duplicates)) {
            echo "\n=== Potential N+1 Queries ===\n";
            foreach ($duplicates as $pattern => $count) {
                echo "  - {$pattern} (x{$count})\n";
            }
        }

        echo "===========================\n\n";
    }
}
