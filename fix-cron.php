<?php
/**
 * Temporary script to fix Gold Calculator Chatbot cron jobs
 * Run this once, then delete this file
 */

// Clear existing cron jobs
wp_clear_scheduled_hook('gcc_price_sync_cron');
wp_clear_scheduled_hook('gcc_exchange_sync_cron');

// Add custom intervals
if (!function_exists('gcc_add_cron_intervals')) {
    function gcc_add_cron_intervals($schedules) {
        $schedules['gcc_every_minute'] = array(
            'interval' => 60,
            'display'  => 'Every Minute'
        );
        
        $schedules['gcc_every_2_hours'] = array(
            'interval' => 7200, // 2 hours = 2 * 60 * 60 = 7200 seconds
            'display'  => 'Every 2 Hours'
        );
        
        return $schedules;
    }
}

add_filter('cron_schedules', 'gcc_add_cron_intervals');

// Schedule new cron jobs
$result1 = wp_schedule_event(time(), 'gcc_every_minute', 'gcc_price_sync_cron');
$result2 = wp_schedule_event(time(), 'gcc_every_2_hours', 'gcc_exchange_sync_cron');
?>
