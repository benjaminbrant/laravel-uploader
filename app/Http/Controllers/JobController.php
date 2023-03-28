<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobController extends Controller
{
    public function index(Request $request)
    {
        //Return invoices associated with specified job id
        if ($request->has('job'))
        {
            $job = $request->job;

            $invoices = Job::findOrFail($job)
                ->invoices()
                ->where('archive_location', '!=', NULL)
                ->paginate(15);

            return Inertia::render('Invoices/Index', ['invoices' => $invoices]);
        }

        //Return all jobs
        $jobs = Job::latest()->paginate(10);

        return Inertia::render('Jobs/Index', ['jobs' => $jobs]);
    }
}
