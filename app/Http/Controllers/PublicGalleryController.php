<?php

namespace App\Http\Controllers;

use App\Models\MissingPerson;

class PublicGalleryController extends Controller
{
    public function index()
    {
        $missingPersons = MissingPerson::with('primaryPhoto')
            ->active()
            ->whereHas('photos')
            ->latest()
            ->paginate(24);
        $missingPersons->getCollection()->each(fn ($person) => $this->redact($person));

        return view('gallery.index', compact('missingPersons'));
    }

    public function show(MissingPerson $missingPerson)
    {
        abort_unless($missingPerson->status === \App\Enums\MissingPersonStatus::Active, 404);
        $this->redact($missingPerson);
        $missingPerson->load(['photos', 'primaryPhoto']);

        return view('gallery.show', compact('missingPerson'));
    }

    private function redact(MissingPerson $person): void
    {
        foreach (['full_name', 'description', 'last_seen_location', 'clothing_description', 'identifying_features'] as $field) {
            $person->$field = \App\Services\Privacy::redact($person->$field);
        }
        $person->date_of_birth = null;
        $person->last_seen_latitude = null;
        $person->last_seen_longitude = null;
    }
}
