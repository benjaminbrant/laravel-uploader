<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::latest()->where('archive_location', '!=', NULL)->paginate(15);

        return Inertia::render('Invoices/Index', ['invoices' => $invoices]);
    }
}
