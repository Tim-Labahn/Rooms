@auth
<div id="legend-toggle" style="position: fixed; bottom: 20px; left: 20px; z-index: 1000; background: white; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: none; justify-content: center; align-items: center; cursor: pointer; border: 1px solid #eee; font-size: 20px;" onclick="toggleLegend()">
    ℹ️
</div>

<div id="color-legend" style="position: fixed; bottom: 20px; left: 20px; z-index: 1001; background: white; padding: 15px; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border: 1px solid #eee; min-width: 200px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="width: 10px; height: 10px; border-radius: 50%; background: #007bff;"></div>
            <strong style="font-size: 14px; color: #333;">Room Status</strong>
        </div>
        <div style="cursor: pointer; font-size: 20px; color: #ccc;" onclick="toggleLegend()">&times;</div>
    </div>
    <div style="display: flex; flex-direction: column; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 10px; font-size: 13px;">
            <div style="width: 18px; height: 18px; border-radius: 4px; border: 2px solid #28a745; background: #f4faf6;"></div>
            <span>Available</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; font-size: 13px;">
            <div style="width: 18px; height: 18px; border-radius: 4px; border: 2px solid #ffc107; background: #fffdf5;"></div>
            <span>Occupied</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; font-size: 13px;">
            <div style="width: 18px; height: 18px; border-radius: 4px; border: 2px solid #dc3545; background: #fdf5f5;"></div>
            <span>Full</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; font-size: 13px;">
            <div style="width: 18px; height: 18px; border-radius: 4px; border: 2px solid #6c757d; background: #f8f9fa;"></div>
            <span>Permanent / Owned</span>
        </div>
    </div>
</div>

<script>
    function toggleLegend() {
        const legend = document.getElementById('color-legend');
        const toggle = document.getElementById('legend-toggle');
        if (legend.style.display === 'none') {
            legend.style.display = 'block';
            toggle.style.display = 'none';
            localStorage.setItem('legend_closed', '0');
        } else {
            legend.style.display = 'none';
            toggle.style.display = 'flex';
            localStorage.setItem('legend_closed', '1');
        }
    }

    // Initialize legend state from localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const legendClosed = localStorage.getItem('legend_closed');
        const legend = document.getElementById('color-legend');
        const toggle = document.getElementById('legend-toggle');

        if (legendClosed === '1') {
            legend.style.display = 'none';
            toggle.style.display = 'flex';
        } else {
            legend.style.display = 'block';
            toggle.style.display = 'none';
        }
    });
</script>
@endauth
