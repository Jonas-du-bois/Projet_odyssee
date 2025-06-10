<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\QuizInstance;
use App\Models\UserQuizScore;
use App\Events\QuizCompleted;
use App\Listeners\SynchronizeUserScore;

class TestPostgreSQLCompatibility extends Command
{    /**
     * The name and signature of the console command.
     */
    protected $signature = 'breitling:test-postgresql {--detailed : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Test PostgreSQL compatibility for Breitling League';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing PostgreSQL compatibility for Breitling League...');

        $tests = [
            'Database Connection' => [$this, 'testDatabaseConnection'],
            'Polymorphic Relations' => [$this, 'testPolymorphicRelations'],
            'Event Listeners' => [$this, 'testEventListeners'],
            'Date Functions' => [$this, 'testDateFunctions'],
            'JSON Queries' => [$this, 'testJsonQueries'],
            'Transactions' => [$this, 'testTransactions'],
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $testName => $testMethod) {
            $this->newLine();
            $this->info("Testing: {$testName}");
            
            try {
                $result = call_user_func($testMethod);
                if ($result) {
                    $this->info("✅ {$testName}: PASSED");
                    $passed++;
                } else {
                    $this->error("❌ {$testName}: FAILED");
                }
            } catch (\Exception $e) {
                $this->error("❌ {$testName}: ERROR - " . $e->getMessage());
                if ($this->option('verbose')) {
                    $this->line($e->getTraceAsString());
                }
            }
        }

        $this->newLine();
        $this->info("📊 Test Results: {$passed}/{$total} tests passed");

        if ($passed === $total) {
            $this->info("🎉 All tests passed! PostgreSQL compatibility confirmed.");
            return Command::SUCCESS;
        } else {
            $this->error("⚠️  Some tests failed. Check the issues above.");
            return Command::FAILURE;
        }
    }

    private function testDatabaseConnection(): bool
    {
        $driver = DB::getDriverName();
        $this->line("  Database driver: {$driver}");
        
        if ($driver !== 'pgsql') {
            $this->line("  ⚠️  Not using PostgreSQL (current: {$driver})");
            return false;
        }

        // Test basic query
        $result = DB::select('SELECT version()');
        $this->line("  PostgreSQL version: " . $result[0]->version);
        
        return true;
    }

    private function testPolymorphicRelations(): bool
    {
        // Test if morph map is configured
        $morphMap = \Illuminate\Database\Eloquent\Relations\Relation::morphMap();
        
        if (empty($morphMap)) {
            $this->line("  ❌ Morph map not configured");
            return false;
        }

        $this->line("  ✅ Morph map configured with " . count($morphMap) . " models");
        
        if ($this->option('verbose')) {
            foreach ($morphMap as $alias => $model) {
                $this->line("    - {$alias} => {$model}");
            }
        }

        return true;
    }

    private function testEventListeners(): bool
    {
        $listener = new SynchronizeUserScore();
        
        // Test date format SQL generation
        $reflection = new \ReflectionClass($listener);
        $method = $reflection->getMethod('getDateFormatSQL');
        $method->setAccessible(true);
        
        $sql = $method->invoke($listener);
        
        if (str_contains($sql, 'TO_CHAR')) {
            $this->line("  ✅ PostgreSQL date formatting configured");
            return true;
        } else {
            $this->line("  ❌ PostgreSQL date formatting not detected: {$sql}");
            return false;
        }
    }

    private function testDateFunctions(): bool
    {
        // Test PostgreSQL date functions
        $result = DB::select("SELECT TO_CHAR(NOW(), 'YYYY-MM') as formatted_date");
        $currentMonth = date('Y-m');
        
        if ($result[0]->formatted_date === $currentMonth) {
            $this->line("  ✅ PostgreSQL date functions working");
            return true;
        } else {
            $this->line("  ❌ Date function mismatch: expected {$currentMonth}, got {$result[0]->formatted_date}");
            return false;
        }
    }

    private function testJsonQueries(): bool
    {
        try {
            // Test JSON functionality
            DB::select("SELECT '{\"test\": true}'::json as test_json");
            $this->line("  ✅ JSON queries supported");
            return true;
        } catch (\Exception $e) {
            $this->line("  ❌ JSON queries failed: " . $e->getMessage());
            return false;
        }
    }

    private function testTransactions(): bool
    {
        try {
            DB::transaction(function () {
                // Test transaction
                DB::select('SELECT 1');
            });
            
            $this->line("  ✅ Transactions working");
            return true;
        } catch (\Exception $e) {
            $this->line("  ❌ Transaction failed: " . $e->getMessage());
            return false;
        }
    }
}
