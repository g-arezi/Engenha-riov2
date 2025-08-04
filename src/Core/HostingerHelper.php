<?php
/**
 * Hostinger Environment Helper
 * This file provides utilities for detecting and configuring the application
 * specifically for Hostinger's shared hosting environment.
 */

namespace App\Core;

class HostingerHelper {
    /**
     * Detect if running on Hostinger
     * 
     * @return bool
     */
    public static function isHostingerEnvironment() {
        // Check for common Hostinger environment indicators
        // This might need adjustment based on the actual server environment
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        $hostname = gethostname();
        
        return (
            stripos($serverSoftware, 'LiteSpeed') !== false ||
            stripos($hostname, 'hostinger') !== false ||
            file_exists('/etc/hostinger-system-version')
        );
    }
    
    /**
     * Configure the application for Hostinger
     * 
     * @param array $config Application configuration array
     * @return array Modified configuration
     */
    public static function configureForHostinger($config) {
        if (!self::isHostingerEnvironment() && !$config['hostinger']['environment']) {
            return $config;
        }
        
        // Determine base path if not set
        if (empty($config['hostinger']['base_path'])) {
            // Auto-detect the base path based on the document root
            $documentRoot = $_SERVER['DOCUMENT_ROOT'];
            $scriptPath = dirname($_SERVER['SCRIPT_FILENAME']);
            $relativePath = str_replace($documentRoot, '', $scriptPath);
            $config['hostinger']['base_path'] = rtrim($relativePath, '/');
        }
        
        // Adjust session settings for Hostinger if needed
        if (empty($config['session']['save_path'])) {
            // Use the tmp directory which should be writable
            $config['session']['save_path'] = sys_get_temp_dir();
        }
        
        return $config;
    }
    
    /**
     * Set up proper URL handling for Hostinger
     */
    public static function setupUrlHandling() {
        if (!self::isHostingerEnvironment()) {
            return;
        }
        
        // Force HTTPS if available
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['HTTPS'] = 'on';
        }
    }
}
