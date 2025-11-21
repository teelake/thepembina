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
        return { ctx: ctx, width: width, height: height };
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

    function renderLineChart(canvas, config) {
        if (!canvas || !config) return;
        var setup = setupCanvas(canvas);
        var ctx = setup.ctx;
        var width = setup.width;
        var height = setup.height;
        var padding = 50;

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
        ctx.textAlign = 'center';
        ctx.fillStyle = '#475569';
        (config.labels || []).forEach(function (label, index) {
            var x = padding + (chartWidth / Math.max(labelCount - 1, 1)) * index;
            ctx.fillText(label, x, height - padding + 20);
        });

        (config.datasets || []).forEach(function (dataset, datasetIndex) {
            ctx.beginPath();
            ctx.lineWidth = dataset.secondary ? 2 : 3;
            ctx.strokeStyle = dataset.color || '#F97316';
            dataset.data.forEach(function (value, index) {
                var x = padding + (chartWidth / Math.max(labelCount - 1, 1)) * index;
                var y = height - padding - (value / maxValue) * chartHeight;
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            ctx.stroke();
        });
    }

    function renderBarChart(canvas, config) {
        if (!canvas || !config) return;
        var setup = setupCanvas(canvas);
        var ctx = setup.ctx;
        var width = setup.width;
        var height = setup.height;
        var padding = 50;

        drawAxes(ctx, width, height, padding);

        var data = config.data || [];
        var labels = config.labels || [];
        var maxValue = Math.max.apply(null, data.concat([10]));
        var chartHeight = height - padding * 1.5;
        var barWidth = (width - padding * 1.5) / (data.length || 1) - 10;

        ctx.font = '12px sans-serif';
        ctx.textAlign = 'center';
        data.forEach(function (value, index) {
            var barHeight = (value / maxValue) * chartHeight;
            var x = padding + index * (barWidth + 10);
            var y = height - padding - barHeight;
            ctx.fillStyle = config.color || '#0EA5E9';
            ctx.fillRect(x, y, barWidth, barHeight);

            ctx.fillStyle = '#1F2937';
            ctx.fillText(value, x + barWidth / 2, y - 6);
            ctx.fillStyle = '#475569';
            ctx.fillText(labels[index] || '', x + barWidth / 2, height - padding + 20);
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

