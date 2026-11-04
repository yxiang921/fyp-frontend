<label class="flex gap-3 items-start my-4">
    <input type="checkbox" name="consent" value="1" required {{ old('consent') ? 'checked' : '' }}>
    <span>{{ __('I consent to sharing this report and any evidence with authorised officers for locating the missing person. Witness details and evidence are private. Active case photos and identifying descriptions may be public. Do not include contact details in public descriptions.') }}
    {{ __('Evidence and witness details are retained for :days days.', ['days' => config('reporting.evidence_retention_days')]) }}</span>
</label>
