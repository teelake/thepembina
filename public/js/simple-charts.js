(function () {
    function setupCanvas(canvas) {
        var ratio = window.devicePixelRatio || 1;
        var width = canvas.clientWidth || 600;
        var height = canvas.clientHeight || 320;
        canvas.width = width * ratio;
        canvas.height = height * ratio;
        var ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);
        ctx.clearRect(0, 0, width, height);
        return { ctx: ctx, width: width, height: height, canvas: canvas };
    }

    function drawAxes(ctx, width, height, padding) {
        ctx.strokeStyle = '#E2E8F0';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(padding, padding / 2);
        ctx.lineTo(padding, height - padding);
        ctx.lineTo(width - padding / 2, height - padding);
        ctx.stroke();
    }

    function createTooltip() {
        var tooltip = document.createElement('div');
        tooltip.id = 'chart-tooltip';
        tooltip.style.cssText = 'position: absolute; background: rgba(0, 0, 0, 0.9); color: white; padding: 8px 12px; border-radius: 6px; font-size: 12px; pointer-events: none; z-index: 1000; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.3);';
        document.body.appendChild(tooltip);
        return tooltip;
    }

    function showTooltip(tooltip, x, y, text) {
        tooltip.textContent = text;
        tooltip.style.display = 'block';
        tooltip.style.left = (x + 10) + 'px';
        tooltip.style.top = (y - 10) + 'px';
    }

    function hideTooltip(tooltip) {
        tooltip.style.display = 'none';
    }

    function renderLineChart(canvas, config) {
        if (!canvas || !config) return;
        var setup = setupCanvas(canvas);
        var ctx = setup.ctx;
        var width = setup.width;
        var height = setup.height;
        var padding = 50;
        var tooltip = document.getElementById('chart-tooltip') || createTooltip();
        var hoverIndex = -1;

        drawAxes(ctx, width, height, padding);

        var allValues = [];
        (config.datasets || []).forEach(function (dataset) {
            allValues = allValues.concat(dataset.data || []);
        });
        var maxValue = Math.max.apply(null, allValues.concat([10]));
        var chartHeight = height - padding * 1.5;
        var chartWidth = width - padding * 1.5;

        ctx.font = '12px sans-serif';
        ctx.fillStyle = '#94A3B8';
        ctx.textAlign = 'right';
        for (var i = 0; i <= 4; i++) {
            var yValue = (maxValue / 4) * i;
            var y = height - padding - (chartHeight / 4) * i;
            ctx.fillText(formatNumber(yValue), padding - 10, y + 4);
            ctx.strokeStyle = '#F1F5F9';
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(width - padding / 2, y);
            ctx.stroke();
        }

        var labelCount = (config.labels || []).length;
        var maxLabels = config.maxLabels || 12;
        var labelStep = Math.max(1, Math.ceil(labelCount / maxLabels));
        ctx.textAlign = 'center';
        ctx.fillStyle = '#475569';
        ctx.font = '11px sans-serif';
        (config.labels || []).forEach(function (label, index) {
            var x = padding + (chartWidth / Math.max(labelCount - 1, 1)) * index;
            if (index % labelStep === 0 || index === labelCount - 1) {
                ctx.fillText(label, x, height - padding + 20);
            }
        });

        // Store data points for tooltip interaction
        var dataPoints = [];

        (config.datasets || []).forEach(function (dataset, datasetIndex) {
            ctx.beginPath();
            ctx.lineWidth = dataset.secondary ? 2 : 3;
            ctx.strokeStyle = dataset.color || '#F97316';
            var points = [];
            dataset.data.forEach(function (value, index) {
                var x = padding + (chartWidth / Math.max(labelCount - 1, 1)) * index;
                var y = height - padding - (value / maxValue) * chartHeight;
                points.push({ x: x, y: y, value: value, label: config.labels[index] });
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            dataPoints.push({ points: points, label: dataset.label, color: dataset.color, ySuffix: config.ySuffix || '' });
            ctx.stroke();

            // Draw points
            points.forEach(function (point) {
                ctx.beginPath();
                ctx.arc(point.x, point.y, 4, 0, 2 * Math.PI);
                ctx.fillStyle = dataset.color || '#F97316';
                ctx.fill();
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();
            });
        });

        // Mouse interaction for tooltips
        canvas.addEventListener('mousemove', function (e) {
            var rect = canvas.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var minDistance = Infinity;
            var closestPoint = null;
            var closestDataset = null;

            dataPoints.forEach(function (dataset) {
                dataset.points.forEach(function (point) {
                    var distance = Math.sqrt(Math.pow(x - point.x, 2) + Math.pow(y - point.y, 2));
                    if (distance < 20 && distance < minDistance) {
                        minDistance = distance;
                        closestPoint = point;
                        closestDataset = dataset;
                    }
                });
            });

            if (closestPoint && closestDataset) {
                var value = closestPoint.value;
                var formattedValue = closestDataset.ySuffix === '$' ? '$' + value.toFixed(2) : value;
                var tooltipText = closestDataset.label + ': ' + formattedValue + '\n' + closestPoint.label;
                showTooltip(tooltip, e.clientX, e.clientY, tooltipText);
                canvas.style.cursor = 'pointer';
            } else {
                hideTooltip(tooltip);
                canvas.style.cursor = 'default';
            }
        });

        canvas.addEventListener('mouseleave', function () {
            hideTooltip(tooltip);
            canvas.style.cursor = 'default';
        });
    }

    function renderBarChart(canvas, config) {
        if (!canvas || !config) return;
        var setup = setupCanvas(canvas);
        var ctx = setup.ctx;
        var width = setup.width;
        var height = setup.height;
        var padding = 50;
        var tooltip = document.getElementById('chart-tooltip') || createTooltip();

        drawAxes(ctx, width, height, padding);

        var data = config.data || [];
        var labels = config.labels || [];
        var maxValue = Math.max.apply(null, data.concat([10]));
        var chartHeight = height - padding * 1.5;
        var barWidth = (width - padding * 1.5) / (data.length || 1) - 10;
        var bars = [];

        ctx.font = '12px sans-serif';
        ctx.textAlign = 'center';
        data.forEach(function (value, index) {
            var barHeight = (value / maxValue) * chartHeight;
            var x = padding + index * (barWidth + 10);
            var y = height - padding - barHeight;
            
            // Store bar position for tooltip
            bars.push({ x: x, y: y, width: barWidth, height: barHeight, value: value, label: labels[index] || '' });
            
            ctx.fillStyle = config.color || '#0EA5E9';
            ctx.fillRect(x, y, barWidth, barHeight);

            ctx.fillStyle = '#1F2937';
            ctx.fillText(value, x + barWidth / 2, y - 6);
            ctx.fillStyle = '#475569';
            ctx.font = '11px sans-serif';
            ctx.fillText(labels[index] || '', x + barWidth / 2, height - padding + 20);
        });

        // Mouse interaction for tooltips
        canvas.addEventListener('mousemove', function (e) {
            var rect = canvas.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var hoveredBar = null;

            bars.forEach(function (bar) {
                if (x >= bar.x && x <= bar.x + bar.width && y >= bar.y && y <= height - padding) {
                    hoveredBar = bar;
                }
            });

            if (hoveredBar) {
                var tooltipText = hoveredBar.label + ': ' + hoveredBar.value + ' orders';
                showTooltip(tooltip, e.clientX, e.clientY, tooltipText);
                canvas.style.cursor = 'pointer';
            } else {
                hideTooltip(tooltip);
                canvas.style.cursor = 'default';
            }
        });

        canvas.addEventListener('mouseleave', function () {
            hideTooltip(tooltip);
            canvas.style.cursor = 'default';
        });
    }

    function formatNumber(value) {
        if (value >= 1000) {
            return (value / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
        }
        return Math.round(value).toString();
    }

    window.SimpleCharts = {
        renderLineChart: renderLineChart,
        renderBarChart: renderBarChart
    };
})();
