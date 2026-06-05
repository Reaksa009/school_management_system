<?php

namespace App\Models;

$connection = strtolower((string) (getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? '')));
$mongoDsn = (string) (getenv('DB_URI') ?: getenv('MONGODB_URI') ?: ($_ENV['DB_URI'] ?? $_ENV['MONGODB_URI'] ?? $_SERVER['DB_URI'] ?? $_SERVER['MONGODB_URI'] ?? ''));

if ($connection === 'mongodb' || ($connection === '' && $mongoDsn !== '')) {
    abstract class BaseAuthenticatable extends \MongoDB\Laravel\Auth\User
    {
    }
} else {
    abstract class BaseAuthenticatable extends \Illuminate\Foundation\Auth\User
    {
    }
}
