<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Models\FaqItem;
use Illuminate\Http\Request;

class FaqItemController extends Controller
{
    public function index(Request $request)
    {
        $query = FaqItem::with('category')->latest();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('question', 'like', "%{$search}%");
        }
        if ($request->filled('category_id')) {
            $query->where('faq_category_id', $request->category_id);
        }
        $faqs = $query->paginate(15)->appends($request->all());
        $categories = FaqCategory::all();
        
        return view('admin.faq_items.index', compact('faqs', 'categories'));
    }

    public function create()
    {
        $categories = FaqCategory::all();
        return view('admin.faq_items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'faq_category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        FaqItem::create([
            'faq_category_id' => $request->faq_category_id,
            'question' => $request->question,
            'answer' => $request->answer,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.faq-items.index')->with('success', 'FAQ Item created successfully.');
    }

    public function edit(FaqItem $faqItem)
    {
        $categories = FaqCategory::all();
        return view('admin.faq_items.edit', compact('faqItem', 'categories'));
    }

    public function update(Request $request, FaqItem $faqItem)
    {
        $request->validate([
            'faq_category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $faqItem->update([
            'faq_category_id' => $request->faq_category_id,
            'question' => $request->question,
            'answer' => $request->answer,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.faq-items.index')->with('success', 'FAQ Item updated successfully.');
    }

    public function destroy(FaqItem $faqItem)
    {
        $faqItem->delete();
        return redirect()->route('admin.faq-items.index')->with('success', 'FAQ Item deleted successfully.');
    }
}
