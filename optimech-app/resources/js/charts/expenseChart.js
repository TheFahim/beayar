import ApexCharts from "apexcharts";

document.addEventListener('alpine:init', () => {
    Alpine.data('expenseChartComponent', () => ({
        // --- STATE ---
        chart: null, // This will hold the ApexCharts instance.
        buttonText: 'This Month',
        isDropdownOpen: false,

        // --- LIFECYCLE HOOK ---
        init() {
            // $nextTick ensures we run this after Alpine has initialized the DOM.
            this.$nextTick(() => {
                // 1. Create the empty chart shell first.
                this.createChart();
                // 2. Fetch the initial data to populate the chart.
                this.fetchAndUpdateChart('this_month');
            });
        },

        // --- METHODS ---

        /**
         * Creates the initial chart instance. This is called only once.
         */
        createChart() {
            const chartEl = document.getElementById('expense-chart');

            if (!chartEl || typeof ApexCharts === 'undefined') {
                console.error("CRITICAL: Chart container #expense-chart or ApexCharts library not found.");
                if (chartEl) chartEl.innerHTML = '<p class="text-red-500 text-center py-12">Charting library failed to load.</p>';
                return;
            }

            // Initial options for the empty chart.
            const initialOptions = {
                series: [],
                chart: {
                    height: 320,
                    width: "100%",
                    type: "donut",
                },
                noData: {
                    text: 'Initializing Chart...' // A temporary message.
                },
                legend: {
                    position: "bottom"
                }
            };

            this.chart = new ApexCharts(chartEl, initialOptions);
            this.chart.render();
        },

        /**
         * Handles period selection from the dropdown.
         */
        selectPeriod(period, text) {
            this.buttonText = text;
            this.isDropdownOpen = false;
            this.fetchAndUpdateChart(period);
        },

        /**
         * The CORE LOGIC: Fetches data and updates the chart.
         */
        async fetchAndUpdateChart(period) {
            // Guard clause: If the chart object doesn't exist, do nothing.
            if (!this.chart) {
                console.error("Cannot update: Chart instance is not available.");
                return;
            }

            // THIS IS THE FIX: Manually set a loading state before the fetch.
            // We clear the series and show a "Loading..." message.
            this.chart.updateOptions({
                series: [],
                labels: [],
                noData: { text: 'Loading data...' }
            });

            try {
                // Fetch the data from the server.
                const response = await fetch(`/dashboard/expenses-chart-data?period=${period}`);
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.statusText}`);
                }
                const data = await response.json();

                // If the fetch is successful, update the chart with the real data.
                const finalOptions = this.getChartOptions(data);
                this.chart.updateOptions(finalOptions);

            } catch (error) {
                console.error('Failed to fetch or update chart data:', error);
                // If the fetch fails, update the chart to show an error message.
                this.chart.updateOptions({
                    series: [],
                    labels: [],
                    noData: { text: 'Failed to load chart data.' }
                });
            }
        },

        /**
         * Helper function to build the complete ApexCharts options object from server data.
         */
        getChartOptions(data) {
            return {
                series: data.series || [],
                labels: data.labels || [],
                colors: ["#1C64F2", "#16BDCA", "#FDBA8C", "#E74694", "#9061F9", "#7E3AF2"],
                chart: {
                    height: 320,
                    width: "100%",
                    type: "donut",
                },
                stroke: {
                    colors: ["transparent"],
                    width: 2,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                name: { show: true, fontFamily: "Inter, sans-serif", offsetY: 20 },
                                total: {
                                    showAlways: true,
                                    show: true,
                                    label: "Total Expenses",
                                    fontFamily: "Inter, sans-serif",
                                    color: "#64748B",
                                    formatter: () => `${data.total} ৳`
                                },
                                value: {
                                    show: true,
                                    fontFamily: "Inter, sans-serif",
                                    color: "#64748B",
                                    offsetY: -20,
                                    formatter: (value) => `${parseFloat(value).toLocaleString()} ৳`
                                },
                            },
                            size: "80%",
                        },
                    },
                },
                grid: { padding: { top: -2 } },
                dataLabels: { enabled: false },
                legend: {
                    position: "bottom",
                    fontFamily: "Inter, sans-serif",
                    labels: { colors: '#64748B', useSeriesColors: false },
                },
                // We no longer need the noData property here, as it's handled in the fetch function
            };
        }
    }));


    Alpine.data('myQuotationChart', () => ({
        chart: null,
        isLoading: true,
        isDropdownOpen: false,
        years: [],
        selectedYear: null,

        init() {
            this.fetchYears();
        },

        async fetchYears() {
            this.isLoading = true;
            try {
                const response = await fetch("/dashboard/api/my-quotation-years");
                this.years = await response.json();
                if (this.years.length > 0) {
                    this.selectYear(this.years[0]); // Select the most recent year by default
                } else {
                    this.isLoading = false; // No years to show, stop loading
                }
            } catch (error) {
                console.error("Failed to fetch years:", error);
                this.isLoading = false;
            }
        },

        selectYear(year) {
            this.selectedYear = year;
            this.isDropdownOpen = false;
            this.fetchChartData(year);
        },

        async fetchChartData(year) {
            this.isLoading = true;
            try {
                const response = await fetch(`/dashboard/api/my-quotation-summary?year=${year}`);
                const summaryData = await response.json();
                this.renderChart(summaryData);
            } catch (error) {
                console.error(`Failed to fetch summary data for ${year}:`, error);
            } finally {
                this.isLoading = false;
            }
        },

        renderChart(data) {
            if (!this.$refs.myQuotationChart) return;

            // Process data for ApexCharts
            const amounts = data.map(d => d.total_amount);
            const counts = data.map(d => d.quotation_count);

            const options = {
                series: [{
                    name: 'Total Amount',
                    data: amounts
                }],
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: { position: 'top' },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val, { dataPointIndex }) => {
                        const count = counts[dataPointIndex];
                        return count > 0 ? `${count}` : ''; // Show count as label, hide if zero
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#90A4AE"],
                        fontWeight: 'bold'
                    }
                },
                xaxis: {
                    categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    position: 'bottom', // Changed from 'top' for better layout
                    labels: { style: { colors: '#90A4AE', fontFamily: 'Inter, sans-serif' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        show: true, // Keep y-axis labels to show scale
                        style: { colors: '#90A4AE', fontFamily: 'Inter, sans-serif' },
                        formatter: (val) => `৳${val / 1000}k`
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: (val) => `৳ ${val.toLocaleString()}`
                    }
                },
                grid: {
                    borderColor: '#e7e7e7',
                    strokeDashArray: 5,
                    yaxis: { lines: { show: true } },
                    xaxis: { lines: { show: false } }
                },
                title: {
                    // The title is now in the component's HTML, so we can remove it from here
                    text: '',
                }
            };

            if (this.chart) {
                this.chart.updateOptions(options);
            } else {
                this.chart = new ApexCharts(this.$refs.myQuotationChart, options);
                this.chart.render();
            }
        }
    }));


});
