<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Application base controller.
 *
 * Laravel 12 no longer ships this file by default.  We recreate it so that
 * $this->authorize() works in all child controllers (PIC ReportController,
 * TransactionController, etc.) without having to import the Gate facade
 * individually everywhere.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
}