<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContactRequest $request)
    {
        $query = Contact::with(['category', 'tags']);
        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$request->keyword.'%')
                    ->orWhere('email', 'like', '%'.$request->keyword.'%');
            });
        }
        if ($request->gender) {
            $query->where('gender', $request->gender);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }
        $contacts = $query->paginate(7);
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.index');
    }
}
