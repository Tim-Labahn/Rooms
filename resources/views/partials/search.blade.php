{{--
    Search Component
    - Provides real-time suggestions as the user types.
    - Integrated with the main search route.
--}}
<div class="top-center">
    <form action="{{ route('home') }}" method="GET" class="search-form" style="position: relative;">
        <input type="text" name="search" class="search-input" placeholder="Search Room, Feature, or Person" id="main-search" autocomplete="off" value="{{ request('search') }}" required>
        <button type="submit" class="search-btn">Search</button>
        <div id="search-results" class="search-results-dropdown"
             style="display: none; position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1000; margin-top: 8px; max-height: 350px; overflow-y: auto;">
        </div>
    </form>
</div>

<script>
    /**
     * Developer Note: Real-time search suggestion logic.
     * Fetches results from search. Suggestions route and populates the dropdown.
     */
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("main-search");
        const resultsDiv = document.getElementById("search-results");

        if (!searchInput) return;

        searchInput.addEventListener("input", function() {
            const q = this.value;
            if (q.length < 1) {
                resultsDiv.style.display = 'none';
                return;
            }

            fetch(`{{ route('search.suggestions') }}?q=${q}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        resultsDiv.style.display = 'none';
                        resultsDiv.innerHTML = '';
                        return;
                    }

                    resultsDiv.innerHTML = '';
                    data.forEach(item => {
                        const a = document.createElement('a');
                        const editMode = new URLSearchParams(window.location.search).get('edit_mode') === '1' ? '&edit_mode=1' : '';
                        a.href = item.url + (item.url.includes('?') ? '&' : '?') + (editMode ? 'edit_mode=1' : '');
                        a.style.display = 'block';
                        a.style.padding = '12px 15px';
                        a.style.textDecoration = 'none';
                        a.style.color = '#333';
                        a.style.borderBottom = '1px solid #f0f0f0';
                        a.style.transition = 'background 0.2s';

                        a.onmouseover = () => a.style.background = '#f8f9fa';
                        a.onmouseout = () => a.style.background = 'transparent';

                        a.innerHTML = `
                            <div style="font-weight: 700; font-size: 14px;">${item.name}</div>
                            <div style="font-size: 11px; color: #888; margin-top: 2px;">
                                <span style="background: #e9ecef; padding: 1px 6px; border-radius: 4px;">Section: ${item.section}</span>
                                <span style="background: #e9ecef; padding: 1px 6px; border-radius: 4px; margin-left: 4px;">Floor: ${item.floor == 0 ? 'E' : item.floor}</span>
                            </div>
                        `;
                        resultsDiv.appendChild(a);
                    });
                    resultsDiv.style.display = 'block';
                });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                resultsDiv.style.display = 'none';
            }
        });
    });
</script>
