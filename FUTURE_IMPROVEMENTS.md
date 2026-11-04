# Potential future improvements

## Search, matching, and investigation

- Calibrate face-match thresholds on a representative Malaysian dataset and report confidence bands instead of a single unqualified score.
- Add liveness and presentation-attack checks for uploaded and camera images.
- Use multi-frame consensus, pose/blur/occlusion quality gates, and person re-identification to reduce repeated and false alerts.
- Add duplicate-case review queues with side-by-side photos, merge tools, and a full merge audit trail.
- Add relationship graphs connecting cases, sightings, detections, vehicles, phones, cameras, and related reports.
- Add configurable similarity rules for names, aliases, IC numbers, descriptions, and transliteration across Malay, Chinese, and English.

## Patrol operations

- Replace area labels with managed GeoJSON polygons and determine the patrol area from camera/GPS coordinates.
- Support overlapping areas, shift schedules, temporary coverage, escalation areas, and mutual-aid patrol units.
- Add acknowledgement deadlines, dispatch acceptance, en-route/arrived/completed states, and automatic escalation when nobody responds.
- Add patrol availability, capacity, distance, and estimated arrival time to recipient selection.
- Add turn-by-turn directions, safe rendezvous points, and field checklists in the mobile app.
- Provide an operations map showing active units with permission-controlled, short-retention locations.

## Maps and geospatial analysis

- Use a supported production tile provider or self-host vector tiles with monitoring and a service-level agreement.
- Add geocoding and reverse geocoding so addresses and pins stay synchronized.
- Add geofenced camera coverage, route reconstruction, time sliders, clustering controls, and spatial confidence/accuracy circles.
- Add hotspot comparisons by time of day and normalization by camera uptime and foot traffic.

## Privacy, safety, and governance

- Move encryption keys to a managed key service, add key rotation, and separate searchable hashes from encryption keys.
- Add fine-grained permissions for evidence, witness identity, IC numbers, locations, exports, and face embeddings.
- Add dual approval for sensitive exports and case merges, watermark downloads, and tamper-evident audit chains.
- Add consent withdrawal, subject-access, correction, legal-hold, retention-exception, and deletion workflows.
- Automatically blur bystanders, vehicle plates, home addresses, and minors in public media.
- Add configurable public-publication review and publish only minimum necessary information.
- Complete a privacy impact assessment, threat model, facial-recognition policy, bias evaluation, and incident-response plan.

## Evidence and case workflow

- Add resumable large uploads, malware scanning, media metadata extraction, transcoding, thumbnails, and chain-of-custody signatures.
- Preserve original evidence separately from redacted derivatives and record every transformation.
- Add officer tasks, assignments, notes, mentions, approvals, deadlines, and an immutable case timeline.
- Add witness follow-up messaging with masked contact details and one-time secure links.
- Add structured closure reasons, found-person safeguarding, and post-case access restrictions.

## Mobile and accessibility

- Add true background synchronization using platform background tasks, with OS-aware retry and battery/network policies.
- Add conflict resolution, attachment compression, upload progress, and a user-visible retry history.
- Complete translation coverage for every staff and public screen and use professional translation review.
- Add screen-reader testing, WCAG 2.2 AA contrast, dynamic text sizing, reduced motion, keyboard navigation, and low-bandwidth mode.
- Add emergency calling guidance, safety prompts for witnesses, and an option to delay location sharing.

## Notifications and communication

- Add per-channel preferences, quiet hours, severity overrides, delivery receipts, deduplication, and escalation chains.
- Add SMS/WhatsApp or another approved emergency channel with templates and delivery auditing.
- Add alert grouping so the same person/camera event does not overwhelm officers and patrol units.
- Add multilingual notification templates and concise lock-screen-safe messages.

## Analytics and model operations

- Track precision, recall, false positives per demographic/lighting/camera, time-to-review, time-to-dispatch, and time-to-resolution.
- Measure camera uptime, image quality, coverage gaps, cost per useful lead, and alert fatigue.
- Add cohort filters, uncertainty intervals, data-quality flags, and definitions/tooltips for every metric.
- Add model/version tracking, drift monitoring, shadow evaluation, rollback, and reviewer feedback loops.
- Separate operational dashboards from governance reports and restrict small-cohort data to protect privacy.

## Reliability, security, and engineering

- Add end-to-end tests for public reports, offline retries, duplicate review, patrol routing, evidence access, and alert acknowledgement.
- Add load tests for camera bursts and notification fan-out, plus failure drills for mail, push, database, storage, and recognition outages.
- Add health checks, structured logs, distributed tracing, error monitoring, queue dashboards, backups, and restore rehearsals.
- Add rate limits by endpoint and identity, bot protection, upload quotas, antivirus scanning, and dependency/security scanning.
- Use object storage with lifecycle policies, database read replicas where needed, and queued media/recognition processing.
- Add API versioning, OpenAPI documentation, deployment environments, feature flags, CI/CD gates, and zero-downtime migrations.
- Add secrets management, password policy/MFA, session controls, device revocation, and periodic access reviews.

## Integrations and data quality

- Integrate with approved police case systems, camera registries, emergency dispatch, and identity-verification services through audited adapters.
- Add standardized location, agency, camera, and case taxonomies with validation and stewardship workflows.
- Add import/export with schema validation, duplicate prevention, provenance, and reversible dry runs.
- Add data completeness scores and prompts for missing high-value fields.
