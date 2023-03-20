<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::latest()->paginate(3);

        return Inertia::render('Jobs/Index', ['jobs' => $jobs]);
    }
}
