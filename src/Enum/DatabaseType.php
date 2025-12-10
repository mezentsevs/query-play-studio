<?php

namespace App\Enum;

enum DatabaseType: string
{
    case MYSQL = 'mysql';
    case POSTGRESQL = 'postgresql';
    case SQLITE = 'sqlite';
}
