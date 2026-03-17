<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\Lead;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'experts' => Expert::count(),
            'blogs' => Blog::count(),
            'categories' => Category::count(),
            'faqs' => Faq::count(),
            'testimonials' => Testimonial::count(),
            'leads' => Lead::count(),
            'new_leads' => Lead::where('status', 'New')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
