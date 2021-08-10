<?php
/**
 * @var string $code
 * @var string $message
 */
use CodeIgniter\CLI\CLI;

CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
