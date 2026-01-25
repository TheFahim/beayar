import ApexCharts from "apexcharts";
var options = {
    series: [
        {
            name: 'Actual',
            data: [

                {
                    x: '',
                    y: 120,
                    goals: [
                        {
                            name: 'Expected',
                            value: 130,
                            strokeWidth: 5,
                            strokeHeight: 10,
                            strokeColor: '#775DD0'
                        }
                    ],

                }
            ],
        },
    ],
    chart: {
        height: 150,
        type: 'bar',
        foreColor: '#64748B' // Sets the color for text elements across the chart
    },
    plotOptions: {
        bar: {
            horizontal: true,
        },
    },
    colors: ['#00E396'],
    dataLabels: {
        formatter: function (val, opt) {
            const goals =
                opt.w.config.series[opt.seriesIndex].data[opt.dataPointIndex]
                    .goals

            if (goals && goals.length) {
                return `${val} / ${goals[0].value}`
            }
            return val
        },

    },
    legend: {
        show: true,
        showForSingleSeries: true,
        customLegendItems: ['Actual', 'Expected'],
        markers: {
            fillColors: ['#00E396', '#775DD0']
        },
        labels: {
            colors: '#64748B', // This was already correctly set
            useSeriesColors: false
        },
    },
    xaxis: {
        labels: {
            style: {
                colors: '#64748B' // Sets the color for the x-axis labels
            },
            formatter: function (val) {
                if (val >= 1000000) {
                    return (val / 1000000).toFixed(0) + 'M';
                }
                if (val >= 1000) {
                    return (val / 1000).toFixed(0) + 'K';
                }
                return val;
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: '#64748B' // Sets the color for the y-axis labels
            }
        }
    }
};

function fTBCurrency(number) {
    // Return '0 ৳' if the input is null, undefined, or not a valid number.
    if (number === null || typeof number === 'undefined' || isNaN(number)) {
        return '0 ৳';
    }

    // Convert the number to a string with Indian English locale formatting.
    const formattedNumber = Number(number).toLocaleString('en-IN', {
        minimumFractionDigits: 0, // Optional: ensure no decimals for whole numbers
        maximumFractionDigits: 2  // Optional: allow up to 2 decimal places
    });

    return `${formattedNumber} ৳`;
}

function fetchAndRenderChart() {
    try {
        // Fetch data from the API
        fetch('/dashboard/api/target-chart-data')
            .then(response => response.json())
            .then(data => {

                options.series[0].data[0].goals[0].value = data.target || 0;
                options.series[0].data[0].y = data.achived || 0;

                if (document.getElementById("target-chart") && typeof ApexCharts !== 'undefined') {
                    var chart = new ApexCharts(document.querySelector("#target-chart"), options);
                    chart.render();
                }

                // Make sure the formatToBangladeshiCurrency function from above is accessible here.

                if (document.getElementById("target-amount")) {
                    document.getElementById("target-amount").innerText = fTBCurrency(data.target);
                }

                if (document.getElementById("target-achived")) {
                    document.getElementById("target-achived").innerText = fTBCurrency(data.achived);
                }

                if (document.getElementById("target-remaining")) {
                    document.getElementById("target-remaining").innerText = fTBCurrency(data.remaining);
                }


            });
    } catch (error) {
        console.error('Failed to fetch or render chart data:', error);
        this.$refs.chartContainer.innerHTML = '<p class="text-red-500 text-center py-12">Failed to load chart data.</p>';
    }
}

fetchAndRenderChart();
