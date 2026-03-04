/**
 * Manhattan Chart Component
 * Simple SVG-based charting (bar, line)
 */
(function() {
    'use strict';

    window.Manhattan = window.Manhattan || {};
    window.Manhattan.Chart = window.Manhattan.Chart || {};

    /**
     * Initialize all charts on the page
     */
    function initCharts() {
        const charts = document.querySelectorAll('[data-component="chart"]');
        charts.forEach(chart => {
            const id = chart.id;
            if (!id) return;

            const type = chart.dataset.chartType || 'bar';
            const config = window.Manhattan.Chart[id];
            
            if (config) {
                renderChart(chart, type, config);
            }
        });
    }

    /**
     * Render a chart into the container
     */
    function renderChart(container, type, config) {
        const width = parseInt(container.dataset.chartWidth) || 600;
        const height = parseInt(container.dataset.chartHeight) || 300;
        
        if (type === 'bar') {
            renderBarChart(container, config, width, height);
        } else if (type === 'line') {
            renderLineChart(container, config, width, height);
        }
    }

    /**
     * Render a bar chart
     */
    function renderBarChart(container, config, width, height) {
        const padding = 40;
        const chartWidth = width - (padding * 2);
        const chartHeight = height - (padding * 2);
        
        const labels = config.labels || [];
        const series = config.series || [];
        
        if (series.length === 0 || labels.length === 0) {
            container.innerHTML = '<div class="chart-empty">No data available</div>';
            return;
        }

        const maxValue = Math.max(...series[0].data, 1);
        const barWidth = chartWidth / labels.length;
        const barGap = barWidth * 0.2;
        const actualBarWidth = barWidth - barGap;

        let svg = `<svg viewBox="0 0 ${width} ${height}" class="m-chart-svg">`;
        
        // Y-axis
        svg += `<line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" stroke="#ccc" stroke-width="1"/>`;
        
        // X-axis
        svg += `<line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#ccc" stroke-width="1"/>`;
        
        // Bars
        series.forEach((s, seriesIndex) => {
            const color = s.color || '#2196F3';
            s.data.forEach((value, i) => {
                const barHeight = (value / maxValue) * chartHeight;
                const x = padding + (i * barWidth) + (barGap / 2);
                const y = height - padding - barHeight;
                
                svg += `<rect x="${x}" y="${y}" width="${actualBarWidth}" height="${barHeight}" fill="${color}" class="m-chart-bar" data-value="${value}">
                    <title>${labels[i]}: ${value}</title>
                </rect>`;
            });
        });
        
        // Labels
        labels.forEach((label, i) => {
            const x = padding + (i * barWidth) + (barWidth / 2);
            const y = height - padding + 20;
            svg += `<text x="${x}" y="${y}" text-anchor="middle" font-size="12" fill="#666">${label}</text>`;
        });
        
        svg += '</svg>';
        container.innerHTML = svg;
    }

    /**
     * Render a line chart
     */
    function renderLineChart(container, config, width, height) {
        const padding = 40;
        const chartWidth = width - (padding * 2);
        const chartHeight = height - (padding * 2);
        
        const labels = config.labels || [];
        const series = config.series || [];
        
        if (series.length === 0 || labels.length === 0) {
            container.innerHTML = '<div class="chart-empty">No data available</div>';
            return;
        }

        const maxValue = Math.max(...series[0].data, 1);
        const stepX = chartWidth / (labels.length - 1 || 1);

        let svg = `<svg viewBox="0 0 ${width} ${height}" class="m-chart-svg">`;
        
        // Y-axis
        svg += `<line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}" stroke="#ccc" stroke-width="1"/>`;
        
        // X-axis
        svg += `<line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}" stroke="#ccc" stroke-width="1"/>`;
        
        // Lines
        series.forEach((s, seriesIndex) => {
            const color = s.color || '#4CAF50';
            let pathData = '';
            
            s.data.forEach((value, i) => {
                const x = padding + (i * stepX);
                const y = height - padding - ((value / maxValue) * chartHeight);
                
                if (i === 0) {
                    pathData += `M ${x} ${y}`;
                } else {
                    pathData += ` L ${x} ${y}`;
                }
                
                // Data points
                svg += `<circle cx="${x}" cy="${y}" r="4" fill="${color}" class="m-chart-point" data-value="${value}">
                    <title>${labels[i]}: ${value}</title>
                </circle>`;
            });
            
            svg += `<path d="${pathData}" fill="none" stroke="${color}" stroke-width="2" class="m-chart-line"/>`;
        });
        
        // Labels
        labels.forEach((label, i) => {
            const x = padding + (i * stepX);
            const y = height - padding + 20;
            svg += `<text x="${x}" y="${y}" text-anchor="middle" font-size="12" fill="#666">${label}</text>`;
        });
        
        svg += '</svg>';
        container.innerHTML = svg;
    }

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }

    // Expose API
    window.Manhattan.Chart.init = initCharts;
    window.Manhattan.Chart.render = renderChart;
})();
