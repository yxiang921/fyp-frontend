import './bootstrap';

// Image upload preview — shows selected file in an <img> element
window.previewImage = function(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
        preview.src = '';
    }
};

// Geocode a last-seen location and fill the coordinate fields.
window.findCoordinates = async function(button) {
    const form = button.closest('form');
    const locationInput = form?.querySelector('[name="last_seen_location"]');
    const latitudeInput = form?.querySelector('[name="last_seen_latitude"]');
    const longitudeInput = form?.querySelector('[name="last_seen_longitude"]');
    const status = form?.querySelector('[data-location-search-status]');

    if (!locationInput || !latitudeInput || !longitudeInput || !status) return;

    const location = locationInput.value.trim();
    if (!location) {
        status.textContent = 'Enter a location before searching.';
        status.className = 'text-xs text-red-600 mt-1';
        locationInput.focus();
        return;
    }

    const originalLabel = button.innerHTML;
    button.disabled = true;
    button.classList.add('opacity-60', 'cursor-wait');
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Searching...';
    status.textContent = 'Searching OpenStreetMap...';
    status.className = 'text-xs text-gray-500 mt-1';

    try {
        const query = new URLSearchParams({
            q: location,
            format: 'jsonv2',
            limit: '1',
            countrycodes: 'my',
        });
        const response = await fetch('https://nominatim.openstreetmap.org/search?' + query, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) throw new Error('Location service unavailable');

        const results = await response.json();
        if (!results.length) {
            throw new Error('No matching Malaysian location found');
        }

        latitudeInput.value = Number(results[0].lat).toFixed(7);
        longitudeInput.value = Number(results[0].lon).toFixed(7);
        status.textContent = 'Coordinates found: ' + results[0].display_name;
        status.className = 'text-xs text-green-600 mt-1';
    } catch (error) {
        status.textContent = error.message || 'Unable to find coordinates. Enter them manually.';
        status.className = 'text-xs text-red-600 mt-1';
    } finally {
        button.disabled = false;
        button.classList.remove('opacity-60', 'cursor-wait');
        button.innerHTML = originalLabel;
    }
};

// --- Global: Poll for notification unread count every 45s ---
(function () {
    const badge = document.getElementById('header-notif-badge');
    if (!badge) return;

    let lastUnread = badge.textContent ? parseInt(badge.textContent.replace('+', ''), 10) : 0;

    setInterval(async () => {
        try {
            const res = await fetch('/notifications/api/unread-count', {
                headers: { 'Accept': 'application/json' },
            });

            // If the route doesn't exist yet this will 404 — silently ignore
            if (!res.ok) return;

            const data = await res.json();
            const count = data.unread_count;

            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }

            lastUnread = count;
        } catch (e) {
            // Silently fail — polling is non-critical
        }
    }, 45000);
})();
