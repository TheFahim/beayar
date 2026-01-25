import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import { DateRangePicker } from 'flowbite-datepicker';


document.addEventListener('alpine:init', () => {
    Alpine.data('topSummery', () => ({
        // --- Properties ---
        sellRevenue: 0,
        collectableBill: 0,
        buyingPriceLeft: 0,
        buyingPrice: 0,
        totalPaid: 0,
        totalDue: 0,
        totalExpense: 0,
        startDate: '',
        endDate: '',
        dateRangePicker: null,

        // --- Methods ---
        init() {
            // Set default dates for the last 3 months
            const today = new Date();
            const threeMonthsAgo = new Date(new Date().setMonth(today.getMonth() - 2));

            this.startDate = this.formatDate(new Date(threeMonthsAgo.getFullYear(), threeMonthsAgo.getMonth(), 1));
            this.endDate = this.formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));

            // Initialize Flowbite DateRangePicker
            const datepickerEl = document.getElementById('date-range-picker');
            this.dateRangePicker = new DateRangePicker(datepickerEl, {
                format: 'dd/mm/yyyy',
            });

            // Set the default values in the input fields
            document.getElementById('datepicker-range-start').value = this.startDate;
            document.getElementById('datepicker-range-end').value = this.endDate;

            // Initial data fetch
            this.fetchTopSummary(this.startDate, this.endDate);
        },

        handleSearch() {
            const [start, end] = this.dateRangePicker.getDates('yyyy-mm-dd');
            this.fetchTopSummary(start, end);
        },

        fetchTopSummary(startDate, endDate) {
            let api = '/dashboard/api/top-summary';

            // Ensure dates are in yyyy-mm-dd for the API
            const formattedStartDate = this.toApiDate(startDate);
            const formattedEndDate = this.toApiDate(endDate);

            if (formattedStartDate && formattedEndDate) {
                const params = new URLSearchParams({
                    start: formattedStartDate,
                    end: formattedEndDate,
                });
                api = `${api}?${params.toString()}`;
            }

            fetch(api)
                .then(response => response.json())
                .then(data => {
                    this.sellRevenue = data.sellRevenue;
                    this.collectableBill = data.collectableBill;
                    this.buyingPriceLeft = data.buyingPriceWarning;
                    this.buyingPrice = data.buyingPrice;
                    this.totalPaid = data.totalPaid;
                    this.totalDue = data.totalDue;
                    this.totalExpense = data.totalExpense;


                })
                .catch(error => console.error('Error fetching top summary:', error));
        },

        // --- Helper Functions ---
        formatDate(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        },

        toApiDate(dateString) {
            if (!dateString) return null;
            // Converts dd/mm/yyyy to yyyy-mm-dd
            if (dateString.includes('/')) {
                const parts = dateString.split('/');
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
            return dateString; // Assumes it's already in yyyy-mm-dd
        }
    }));

    // This is the new component for profit calculation
    Alpine.data('profitCalculator', () => ({
        // --- Properties ---
        totalReceived: 0,
        totalExpense: 0,
        totalPurchasePrice: 0,
        profit: 0,
        vat: 0,
        att: 0,
        deliveryCost: 0,
        loading: false,
        resultsVisible: false,
        dateRangePicker: null,

        // --- Methods ---
        init() {
            // **THE FIX IS HERE:** We are now getting the element with the unique ID
            const datepickerEl = document.getElementById('date-range-picker-profit');

            this.dateRangePicker = new DateRangePicker(datepickerEl, {
                format: 'dd/mm/yyyy',
            });

            // Set default dates in the input fields for user convenience
            const today = new Date();
            const threeMonthsAgo = new Date(new Date().setMonth(today.getMonth() - 2));
            const startDate = new Date(threeMonthsAgo.getFullYear(), threeMonthsAgo.getMonth(), 1);
            const endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            // Use the datepicker's API to set the dates
            this.dateRangePicker.setDates(startDate, endDate);
        },

        handleSearch() {
            // ... the rest of your handleSearch function remains the same ...
            this.loading = true;
            this.resultsVisible = false;

            const [startDate, endDate] = this.dateRangePicker.getDates('yyyy-mm-dd');

            if (!startDate || !endDate) {
                alert('Please select a valid date range.');
                this.loading = false;
                return;
            }

            let api = `/dashboard/api/profit-summary?start=${startDate}&end=${endDate}`;

            // console.log(api);


            fetch(api)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    this.totalReceived = data.totalReceived;
                    this.totalExpense = data.totalExpense;
                    this.totalPurchasePrice = data.totalPurchasePrice;
                    this.profit = data.profit;
                    this.vat = data.totalVat;
                    this.att = data.totalAtt;
                    this.deliveryCost = data.totalDelivery;
                    this.resultsVisible = true;
                })
                .catch(error => {
                    console.error('Error fetching profit summary:', error);
                    alert('Failed to fetch profit data. Please check the console.');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    }));


    Alpine.data('expenseAdmin', () => ({
        isDropdownOpen: false,
        chart: null,
        expense: 0,
        filters: [
            { label: 'This Month', value: 'this_month' },
            { label: 'Last Month', value: 'last_month' },
            { label: 'This Year', value: 'this_year' },
            { label: 'Last Year', value: 'last_year' },
            { label: 'Last 90 days', value: 'last_90_days' },
            { label: 'Last 6 months', value: 'last_6_months' }
        ],
        selectedFilter: { label: 'This Month', value: 'this_month' },

        init() {
            this.fetchExpense();
            this.initChart();
        },

        fetchExpense() {
            const api = `/dashboard/api/expense?filter=${this.selectedFilter.value}`;
            fetch(api)
                .then(response => response.json())
                .then(data => {
                    this.expense = data.total_expense;
                    // Pass the user-specific expense data to the chart
                    this.updateChart(data.expenses);
                })
                .catch(error => console.error('Error fetching expense:', error));
        },

        selectFilter(filter) {
            this.selectedFilter = filter;
            this.isDropdownOpen = false;
            this.fetchExpense();
        },

        initChart() {
            const options = {
                series: [{
                    name: "Expense",
                    data: [],
                    color: "#F05252",
                }],
                chart: {
                    type: "bar",
                    height: 350,
                    toolbar: {
                        show: false
                    },
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        columnWidth: "100%",
                        borderRadius: 6,
                    },
                },
                // --- ADDED/MODIFIED OPTIONS FOR COLORS ---
                dataLabels: {
                    enabled: false,
                },
                grid: {
                    show: true,
                    borderColor: '#90A4AE', // A neutral gray for grid lines
                    strokeDashArray: 4,
                    padding: {
                        left: 2,
                        right: 2,
                        top: -20
                    },
                },
                xaxis: {
                    categories: [],
                    tickAmount: 5,
                    labels: {
                        style: {
                            colors: '#90A4AE', // Color for x-axis labels
                            fontFamily: "Inter, sans-serif",
                        },
                    },
                    axisTicks: { show: true },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#90A4AE', // Color for y-axis labels
                            fontFamily: "Inter, sans-serif",
                        }
                    }
                },
                // --- END OF ADDED/MODIFIED OPTIONS ---
            };
            this.chart = new ApexCharts(document.getElementById("bar-chart"), options);
            this.chart.render();
        },

        // --- MODIFIED FUNCTION ---
        updateChart(expenseData) {
            if (!this.chart) return;

            const categories = expenseData.map(item => item.user_name);
            const seriesData = expenseData.map(item => item.total_expense);

            this.chart.updateOptions({
                xaxis: {
                    categories: categories,
                },
                series: [{
                    data: seriesData,
                }],
                dataLabels: {
                    enabled: true,
                    formatter: (val) => "৳" + val.toLocaleString(),
                    offsetX: -15,
                    style: {
                        fontFamily: "Inter, sans-serif",
                        colors: ["#90A4AE"]
                    },
                    dropShadow: {
                        enabled: true,
                        top: 1,
                        left: 1,
                        blur: 1,
                        color: '#000',
                        opacity: 0.35
                    }
                },
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        const userData = expenseData[dataPointIndex];
                        if (!userData) return '';

                        const typeItems = Object.entries(userData.type)
                            .map(([type, amount]) => {
                                const cleanType = String(type).replace(/</g, "<").replace(/>/g, ">");
                                return `<li style="display: flex; justify-content: space-between; padding: 3px 0;">
                                    <span>${cleanType}:</span>
                                    <span style="font-weight: 600; margin-left: 15px;">৳${amount.toLocaleString()}</span>
                                </li>`;
                            }).join('');

                        return `
                    <div class="apexcharts-tooltip-title" style="font-family: Inter, sans-serif; font-size: 12px; font-weight: bold; background: #ECEFF1; padding: 6px 10px;">
                        ${w.globals.labels[dataPointIndex]}
                    </div>
                    <div style="padding: 8px; font-family: Inter, sans-serif; font-size: 12px;">
                        <div style="margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: bold; font-size: 13px;">Total Expense</span>
                            <span style="font-weight: bold; font-size: 13px; margin-left: 15px;">৳${userData.total_expense.toLocaleString()}</span>
                        </div>
                        ${typeItems ? '<hr style="margin: 4px 0; border-top: 1px solid #e0e0e0;">' : ''}
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            ${typeItems}
                        </ul>
                    </div>
                `;
                    }
                }
            });
        }
    }));

    Alpine.data('dueAdmin', () => ({
        isDropdownOpen: false,
        totalDue: 0,
        users: [],
        filters: [
            { label: 'This Month', value: 'this_month' },
            { label: 'Last Month', value: 'last_month' },
            { label: 'This Year', value: 'this_year' },
            { label: 'Last Year', value: 'last_year' },
            { label: 'Last 90 days', value: 'last_90_days' },
            { label: 'Last 6 months', value: 'last_6_months' }
        ],
        selectedFilter: { label: 'This Month', value: 'this_month' },
        tooltip: {
            show: false,
            text: '',
            x: 0,
            y: 0,
        },
        init() {
            this.fetchDueData();
        },
        fetchDueData() {
            const apiUrl = `/dashboard/api/due?filter=${this.selectedFilter.value}`;
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    this.totalDue = data.total_due;
                    this.users = data.users;
                })
                .catch(error => console.error('Error fetching due data:', error));
        },
        selectFilter(filter) {
            this.selectedFilter = filter;
            this.isDropdownOpen = false;
            this.fetchDueData();
        },
        formatCurrency(value) {
            if (typeof value !== 'number') value = 0;
            return value.toLocaleString('en-US') + ' ৳';
        },
        showTooltip(event, dueAmount, userName) {
            // Set the structured data for the tooltip
            this.tooltip.userName = userName;
            this.tooltip.dueAmount = this.formatCurrency(dueAmount);

            // Position tooltip slightly below and to the right of the cursor
            this.tooltip.x = event.clientX + 15;
            this.tooltip.y = event.clientY + 15;
            this.tooltip.show = true;
        },
        hideTooltip() {
            this.tooltip.show = false;
        }
    }));


    Alpine.data('targetAdmin', () => ({
        isDropdownOpen: false,
        chart: null, // Initialize chart as null
        year: new Date().getFullYear(),
        stats: { target: 0, achieved: 0, remaining: 0 },
        filters: [
            { label: 'This Year', value: 'this_year' },
            { label: 'Last Year', value: 'last_year' },
        ],
        selectedFilter: { label: 'This Year', value: 'this_year' },

        init() {
            // Just fetch the data. The chart will be created inside the fetch function.
            this.fetchTargetData();
        },

        fetchTargetData() {
            const apiUrl = `/dashboard/api/target?filter=${this.selectedFilter.value}`;
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    this.year = data.year;
                    this.stats = data.stats;

                    // Check if the chart has already been initialized
                    if (this.chart) {
                        // If it exists, just update it with the new data
                        this.chart.updateSeries([{ name: 'Actual', data: data.chartData }]);
                    } else {
                        // If it doesn't exist (first run), initialize it with the fetched data
                        this.initChart(data.chartData);
                    }
                })
                .catch(error => console.error('Error fetching target data:', error));
        },

        selectFilter(filter) {
            this.selectedFilter = filter;
            this.isDropdownOpen = false;
            // Fetching new data will now correctly update the existing chart
            this.fetchTargetData();
        },

        initChart(chartData) {
            const options = {
                // Pass the initial data directly into the series
                series: [{ name: 'Actual', data: chartData }],
                chart: {
                    height: 300,
                    type: 'bar',
                    toolbar: { show: false },
                    foreColor: '#64748B'
                },
                plotOptions: { bar: { horizontal: true } },
                colors: ['#00E396'],
                dataLabels: {
                    formatter: function (val, opt) {
                        const goals = opt.w.config.series[opt.seriesIndex].data[opt.dataPointIndex]?.goals;
                        if (goals && goals.length) {
                            const format = n => (n >= 1000000 ? (n / 1000000).toFixed(1) + 'M' : (n >= 1000 ? (n / 1000).toFixed(0) + 'K' : n));
                            return `${format(val)} / ${format(goals[0].value)}`;
                        }
                        return val;
                    },
                },
                legend: {
                    show: true,
                    showForSingleSeries: true,
                    customLegendItems: ['Achieved', 'Target'],
                    markers: { fillColors: ['#00E396', '#775DD0'] },
                },
                xaxis: {
                    labels: {
                        formatter: function (val) {
                            if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                            if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
                            return val;
                        }
                    }
                }
            };
            // Create the chart instance and store it
            this.chart = new ApexCharts(document.querySelector("#target-chart"), options);
            // Finally, render the chart
            this.chart.render();
        },

        formatCurrency(value) {
            if (typeof value !== 'number') value = 0;
            return value.toLocaleString('en-US');
        }
    }));

    Alpine.data('adminQuotation', () => ({
        isDropdownOpen: false,
        filters: [
            { label: 'This Month', value: 'this_month' },
            { label: 'This Year', value: 'this_year' },
            { label: 'Last Year', value: 'last_year' },
            { label: 'All Time', value: 'all' },
        ],
        selectedFilter: { label: 'This Month', value: 'this_month' },
        chart: null, // <-- 1. Added property to hold the chart instance

        init() {
            this.fetchAndRenderChart();
        },

        selectFilter(filter) {
            this.selectedFilter = filter;
            this.isDropdownOpen = false;
            this.fetchAndRenderChart();
        },

        rawData: [],

        async fetchAndRenderChart() {
            try {
                const response = await fetch(`/dashboard/api/quotation?filter=${this.selectedFilter.value}`);
                if (!response.ok) throw new Error('Network response was not ok');
                const fetchedData = await response.json();

                const rawData = fetchedData.map(data => {
                    return {
                        user: data.user.name,
                        quotationId: data.quotation_no,
                        amount: data.total
                    }
                });

                this.updateChart(rawData);
            } catch (error) {
                console.error("Failed to fetch chart data:", error);
                // Optionally, show an error message in the UI
            }
        },

        processChartData(data) {
            const dataByUser = {};
            data.forEach(item => {
                if (!dataByUser[item.user]) {
                    dataByUser[item.user] = [];
                }
                dataByUser[item.user].push({ amount: item.amount, id: item.quotationId });
            });

            const userCategories = Object.keys(dataByUser);
            let maxQuotes = 0;

            const yAxisLabels = userCategories.map(user => {
                const count = dataByUser[user].length;
                if (count > maxQuotes) maxQuotes = count;
                return `${user} (${count})`;
            });

            const series = [];
            const quoteIdLookup = [];

            for (let i = 0; i < maxQuotes; i++) {
                const seriesData = [];
                const idData = [];
                userCategories.forEach(user => {
                    if (dataByUser[user][i]) {
                        seriesData.push(dataByUser[user][i].amount);
                        idData.push(dataByUser[user][i].id);
                    } else {
                        seriesData.push(null);
                        idData.push(null);
                    }
                });
                series.push({ name: ` ${i + 1}`, data: seriesData });
                quoteIdLookup.push(idData);
            }
            return { series, yAxisLabels, quoteIdLookup, dataByUser };
        },

        updateChart(rawData) {
            // Ensure the chart container element exists
            if (!this.$refs.quotationChart) return;

            const { series, yAxisLabels, quoteIdLookup, dataByUser } = this.processChartData(rawData);

            const options = {
                series: series,
                chart: {
                    type: 'bar',
                    height: 450,
                    stacked: true,
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                    },
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: yAxisLabels,
                    title: {
                        text: 'Total Quotation Amount (৳)',
                        style: { fontFamily: "Inter, sans-serif", color: "#90A4AE" }
                    },
                    labels: {
                        style: { colors: '#90A4AE', fontFamily: "Inter, sans-serif" },
                    }
                },
                yaxis: {
                    title: {
                        text: 'Users',
                        style: { fontFamily: "Inter, sans-serif", color: "#90A4AE" }
                    },
                    labels: {
                        style: { colors: '#90A4AE', fontFamily: "Inter, sans-serif" },
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val, { seriesIndex, dataPointIndex, w }) => {
                            if (val === null) return;
                            const quotationId = quoteIdLookup[seriesIndex][dataPointIndex];
                            const userLabel = w.globals.labels[dataPointIndex];
                            const userName = userLabel.substring(0, userLabel.lastIndexOf('(') - 1);
                            const totalAmount = dataByUser[userName].reduce((sum, quote) => sum + quote.amount, 0);

                            return `
                            <ul style="padding: 10px; margin: 0; list-style: none;">
                                <li>${quotationId}: ৳${val.toLocaleString()}</li>
                                <li>Total: ৳${totalAmount.toLocaleString()}</li>
                            </ul>
                        `;
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    show: false,
                    position: 'top',
                    horizontalAlign: 'left'
                }
            };

            // --- 2. MODIFIED LOGIC ---
            // If the chart is already initialized, just update it for better performance.
            if (this.chart) {
                this.chart.updateOptions(options);
            } else {
                // Otherwise, create a new chart instance.
                this.chart = new ApexCharts(this.$refs.quotationChart, options);
                this.chart.render();
            }
        }
    }));


});
