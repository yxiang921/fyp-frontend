<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindSight Alert Dispatch</title>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 540px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">

        {{-- Header --}}
        <div style="background: #1e3a5f; padding: 24px; text-align: center;">
            <h1 style="color: #fff; margin: 0; font-size: 18px; font-weight: 600;">
                &#128680; FindSight Alert Dispatch
            </h1>
            <p style="color: #93a3b8; margin: 4px 0 0; font-size: 12px;">
                FindSight
            </p>
        </div>

        {{-- Body --}}
        <div style="padding: 24px;">

            <p style="margin: 0 0 16px; font-size: 14px; color: #374151;">
                An alert has been dispatched for your attention. Please review the details below and take appropriate action.
            </p>

            <table style="width: 100%; border-collapse: collapse; font-size: 13px; color: #374151;">
                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600; width: 140px; border-bottom: 1px solid #e5e7eb;">Alert ID</td>
                    <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-family: monospace;">{{ $alert->alert_id }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Case Number</td>
                    <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-family: monospace;">{{ $missingPerson->case_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Subject</td>
                    <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb;">{{ $missingPerson->full_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Similarity Score</td>
                    <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #16a34a;">
                        {{ $alert->similarity_score }}%
                    </td>
                </tr>

                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Status</td>
                    <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb;">{{ $alert->status->label() }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Camera</td>
                    <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb;">{{ $camera->name }} &mdash; {{ $camera->location }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e5e7eb;">Detection ID</td>
                    <td style="padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-family: monospace;">{{ $detection->detection_id }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; background: #f9fafb; font-weight: 600;">Detected At</td>
                    <td style="padding: 8px 12px;">{{ $alert->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            </table>

            {{-- Alert Log / Timeline --}}
            @if($timeline->isNotEmpty())
            <h2 style="margin: 24px 0 12px; font-size: 15px; color: #1e3a5f; border-bottom: 2px solid #e5e7eb; padding-bottom: 6px;">Alert Log</h2>
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #374151;">
                <thead>
                    <tr style="background: #f9fafb;">
                        <th style="padding: 6px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Time</th>
                        <th style="padding: 6px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Event</th>
                        <th style="padding: 6px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeline as $event)
                    <tr>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #f3f4f6; white-space: nowrap;">{{ $event->created_at->format('d M Y, H:i') }}</td>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #f3f4f6; font-weight: 500;">{{ $event->event_type }}</td>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #f3f4f6;">{{ $event->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <p style="margin: 20px 0 0; font-size: 12px; color: #6b7280;">
                This is a manually dispatched alert from FindSight. Please do not reply to this email. Log in to the system to review and take action on this alert.
            </p>
        </div>

        {{-- Footer --}}
        <div style="background: #f9fafb; padding: 16px 24px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="margin: 0; font-size: 11px; color: #9ca3af;">
                &copy; {{ date('Y') }} FindSight
            </p>
        </div>
    </div>
</body>
</html>
