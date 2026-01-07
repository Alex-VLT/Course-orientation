<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display the contact form.
     */
    public function show()
    {
        return view('pages.contact');
    }

    /**
     * Handle the contact form submission.
     */
    public function send(Request $request)
    {
        // 1. Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string|min:10',
        ]);


        return back()->with('success', 'Thank you! Your message has been sent successfully.');
    }
}