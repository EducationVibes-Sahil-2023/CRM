<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LogQueries {

    public function log() {
        $CI =& get_instance();

        try {
            // Ensure the queries array exists
            if (!isset($CI->db->queries) || !is_array($CI->db->queries)) {
                log_message('error', 'Query logging failed: Queries array not set.');
                return;
            }

            // Get queries and execution times
            $queries = $CI->db->queries;
            $execution_times = $CI->db->query_times;

            // Define log file paths
            $log_dir = APPPATH . 'logs/';
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }

            $slow_query_log_file = $log_dir . 'slow_queries-' . date('Y-m-d') . '.log';
            $error_log_file = $log_dir . 'query_errors-' . date('Y-m-d') . '.log';
            $execution_time_log_file = $log_dir . 'execution_times-' . date('Y-m-d') . '.log';

            foreach ($queries as $key => $query) {
                // Skip INSERT, UPDATE, DELETE queries
                if (preg_match('/^\s*(INSERT)/i', $query)) {
                    continue;
                }

                // Capture execution time
                $execution_time = isset($execution_times[$key]) ? $execution_times[$key] : 'N/A';

                // Capture the fetch time by simulating or measuring it
                $fetch_time = $this->getFetchTime($query);

                // Log executed queries
                $execution_log = "Executed Query Log - " . date('Y-m-d H:i:s') . "\n";
                $execution_log .= "Execution Time: {$execution_time}s\n";
                $execution_log .= "Fetch Time: {$fetch_time}s\n";
                $execution_log .= "Query: {$query}\n";
                $execution_log .= "Backtrace: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)) . "\n\n";
                file_put_contents($execution_time_log_file, $execution_log, FILE_APPEND);

                // Log slow queries (execution time > 1 second)
                if ($execution_time !== 'N/A' && $execution_time > 1.0 || $fetch_time !== 'N/A' && $fetch_time > 1.0) {
                    $slow_query_log = "Slow Query Log - " . date('Y-m-d H:i:s') . "\n";
                    $slow_query_log .= "Execution Time: {$execution_time}s\n";
                    $slow_query_log .= "Fetch Time: {$fetch_time}s\n";
                    $slow_query_log .= "Query: {$query}\n";
                    $slow_query_log .= "Backtrace: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)) . "\n\n";
                    file_put_contents($slow_query_log_file, $slow_query_log, FILE_APPEND);
                }
            }
        } catch (Exception $e) {
            // Log exceptions with details
            $error_log = "Exception Log - " . date('Y-m-d H:i:s') . "\n";
            $error_log .= "Message: " . $e->getMessage() . "\n";
            $error_log .= "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
            $error_log .= "Trace: " . $e->getTraceAsString() . "\n\n";
            file_put_contents($error_log_file, $error_log, FILE_APPEND);
            log_message('error', 'Exception in query logging: ' . $e->getMessage());
        }
    }

    /**
     * Custom function to calculate query fetch time (simulate or extend based on CI DB driver)
     */
    private function getFetchTime($query) {
        // Simulating fetch time as an additional metric (customize based on CI driver capabilities)
        // In real use cases, you might need to benchmark query fetch times manually or use extensions.
        // $start_time = microtime(true);

        // // Execute the query or simulate fetching data
        // $CI =& get_instance();
        // $result = $CI->db->query($query);

        // // Calculate the time taken to fetch the results
        // $end_time = microtime(true);

        // // Return fetch time in seconds (rounded to 4 decimal places)
        // return number_format(($end_time - $start_time), 4);
    }
}
