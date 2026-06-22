<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Tatkal extends BaseConfig
{
    public string $trainNumber = '12345';
    public int $seatsPerCompartment = 72;
    public int $racCapacity = 50;
    public string $openingTime = '00:00:00';
    public int $deadlockRetries = 5;
    public int $workerBatchSize = 250;
    public int $maxWorkers = 20;
}
