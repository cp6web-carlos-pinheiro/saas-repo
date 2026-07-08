<?php

declare(strict_types=1);

return [
    'strategy' => env('TENANCY_STRATEGY', 'single_database_row_scope'),

    'tenant_header' => env('TENANCY_HEADER', 'X-Company-Id'),

    'required_column' => 'company_id',

    'enforce_in_api' => true,

    'strict_membership_check' => true,
];
