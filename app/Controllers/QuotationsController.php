<?php
/**
 * Quotations Controller
 * Extends SafeController with type=quotation
 */

namespace App\Controllers;

class QuotationsController extends SafeController
{
    protected string $type = 'quotation';
    protected string $typeLabel = '';
    protected string $typeIcon = 'fa-file-invoice-dollar';
    protected string $routePrefix = '/quotations';
    protected bool $clientRequired = true;

    public function __construct()
    {
        $this->typeLabel = __('quotations.title');
    }
}
