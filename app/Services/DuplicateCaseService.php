<?php

namespace App\Services;

use App\Models\MissingPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class DuplicateCaseService
{
    public function inspect(array $data, $photo = null): array
    {
        $matches = [];
        $normalize = fn ($value) => mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $value ?? ''));
        $name = $normalize($data['full_name'] ?? '');
        $description = $normalize($data['description'] ?? '');
        $idHash = empty($data['national_id']) ? null : MissingPerson::nationalIdHash($data['national_id']);
        foreach (MissingPerson::whereIn('status', ['pending', 'active', 'reopened'])->cursor() as $person) {
            $reasons = [];
            if ($idHash && hash_equals($person->getRawOriginal('national_id_hash') ?? '', $idHash)) $reasons[] = 'national_id';
            similar_text($name, $normalize($person->full_name), $nameScore);
            similar_text($description, $normalize($person->description), $descriptionScore);
            if ($name && $nameScore >= 90) $reasons[] = 'name';
            if (mb_strlen($description) >= 20 && $descriptionScore >= 85) $reasons[] = 'description';
            if ($reasons) $matches[$person->case_number] = ['case_number' => $person->case_number, 'reasons' => $reasons];
        }
        $faceStatus = $photo ? 'unavailable' : 'no_photo';
        if ($photo && config('services.python_api.api_key')) {
            try {
                $response = Http::timeout(25)->withHeaders(['X-API-Key' => config('services.python_api.api_key')])
                    ->attach('photo', file_get_contents($photo->getRealPath()), 'photo.jpg')
                    ->post(rtrim(config('services.python_api.base_url'), '/').'/api/duplicate-check', [
                        'threshold' => config('reporting.face_duplicate_threshold'),
                    ]);
                if ($response->successful()) {
                    $faceStatus = $response->json('face_status', 'checked');
                    foreach ($response->json('matches', []) as $match) {
                        $case = $match['case_number'];
                        if (!MissingPerson::where('case_number', $case)->whereIn('status', ['active', 'pending', 'reopened'])->exists()) continue;
                        $matches[$case] ??= ['case_number' => $case, 'reasons' => []];
                        $matches[$case]['reasons'][] = 'face';
                        $matches[$case]['face_similarity'] = $match['score'];
                    }
                }
            } catch (\Throwable $e) {
                // An unavailable recognizer is recorded for officer review, never reported as a clean match.
            }
        }
        return ['matches' => array_values($matches), 'face_status' => $faceStatus];
    }

    public function guard(Request $request, array $data): array
    {
        $result = $this->inspect($data, $request->file('photo'));
        if ($result['matches'] && !$request->boolean('duplicate_acknowledged')) {
            throw ValidationException::withMessages(['duplicate_acknowledged' => __('Possible duplicate case. Check existing reports or confirm that this is a separate incident.')]);
        }
        $result['acknowledged'] = $request->boolean('duplicate_acknowledged');
        return $result;
    }
}
