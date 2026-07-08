<?php

use App\Providers\AppServiceProvider;
use App\Providers\ArchitectureServiceProvider;
use App\Providers\ObservabilityServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    ArchitectureServiceProvider::class,
    ObservabilityServiceProvider::class,
    TenancyServiceProvider::class,
];
