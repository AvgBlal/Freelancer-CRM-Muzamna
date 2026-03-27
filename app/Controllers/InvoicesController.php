<?php
/**
 * Invoices Controller
 * Extends SafeController with type=invoice
 */

namespace App\Controllers;

class InvoicesController extends SafeController
{
    protected string $type = 'invoice';
    protected string $typeLabel = '';
    protected string $typeIcon = 'fa-file-invoice';
    protected string $routePrefix = '/invoices';
    protected bool $clientRequired = true;

    public function __construct()
    {
        $this->typeLabel = __('invoices.title');
    }
}
