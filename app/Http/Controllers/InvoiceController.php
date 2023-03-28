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

            try {
                $invoices = Invoice::findOrFail($invoiceId)
                    ->where('archive_location', '!=', NULL)
                    ->paginate(15);


            } catch (ModelNotFoundException $e)
            {
                //Manually set total to zero to display no invoices found message on component
                $invoices = ['total' => 0];
            }
            return Inertia::render('Invoices/Index', ['invoices' => $invoices]);
        }

        //Return all invoices
        $invoices = Invoice::latest()->where('archive_location', '!=', NULL)->paginate(15);

        return Inertia::render('Invoices/Index', ['invoices' => $invoices]);
    }
}
