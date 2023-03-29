<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        //Return specific invoice
        if ($request->has('search'))
        {
            $invoiceId = $request->search;

            $invoices = Invoice::where('po', '=', $invoiceId)
                    ->where('archive_location', '!=', NULL)
                    ->paginate(15);


            return Inertia::render('Invoices/Index', ['invoices' => $invoices]);
        }

        //Return all invoices
        $invoices = Invoice::latest()->where('archive_location', '!=', NULL)->paginate(15);

        return Inertia::render('Invoices/Index', ['invoices' => $invoices]);
    }
}
