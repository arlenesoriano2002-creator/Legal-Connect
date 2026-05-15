

// ====================== SIDEBAR TOGGLE ======================
       // document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
        //    const menuToggle = document.getElementById('menu-toggle');
        //    if (menuToggle) {
          //      menuToggle.addEventListener('click', function() {
        //            document.getElementById('wrapper').classList.toggle('toggled');
         //       });
        //    }
       // });

       if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
            // Show error message or hide chart containers
            document.querySelectorAll('.chart-container').forEach(container => {
                container.innerHTML += `
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Charts cannot be displayed. Please check if Chart.js is loaded.
                    </div>
                `;
            });
        }

        // ====================== LOGOUT MODAL ======================
        function showLogoutModal() {
            // Create modal instance
            const modalElement = document.getElementById('logoutConfirmationModal');
            
            // Remove any aria-hidden attributes that might conflict
            modalElement.removeAttribute('aria-hidden');
            modalElement.setAttribute('aria-modal', 'true');
            
            // Use Bootstrap's modal properly
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: true,
                focus: true
            });
            
            // Show modal
            modal.show();
            
            // Listen for modal events to fix aria attributes
            modalElement.addEventListener('shown.bs.modal', function() {
                // Ensure proper accessibility
                this.removeAttribute('aria-hidden');
                this.setAttribute('aria-modal', 'true');
                
                // Focus on the cancel button
                setTimeout(() => {
                    const cancelBtn = this.querySelector('.btn-secondary');
                    if (cancelBtn) {
                        cancelBtn.focus();
                    }
                }, 100);
            });
            
            modalElement.addEventListener('hidden.bs.modal', function() {
                // When hidden, let Bootstrap handle aria-hidden
                this.removeAttribute('aria-modal');
            });
        }

        // Keyboard shortcut (Ctrl+Q) for logout
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
                e.preventDefault();
                // Find and click the logout button
                const logoutBtn = document.querySelector('.logout-btn[onclick*="showLogoutModal"]');
                if (logoutBtn) {
                    logoutBtn.click();
                } else {
                    // Fallback to calling the function directly
                    showLogoutModal();
                }
            }
        });



        /**
 * Feedback Reports Dashboard JavaScript
 * Handles all interactive functionality for the feedback reports page
 */

class FeedbackReports {
    constructor() {
        this.charts = {};
        this.init();
    }

    init() {
        this.initializeElements();
        this.bindEvents();
        this.initializeCharts();
        this.setupAutoRefresh();
        this.updateFilterListeners();
    }

    initializeElements() {
        // Sidebar toggle
        this.menuToggleBtn = document.getElementById('menu-toggle');
        
        // Action buttons
        this.generatePdfBtn = document.getElementById('generatePdfBtn');
        this.exportCsvBtn = document.getElementById('exportCsvBtn');
        this.refreshDataBtn = document.getElementById('refreshDataBtn');
        
        // Filter elements
        this.ratingFilter = document.getElementById('ratingFilter');
        this.startDateFilter = document.getElementById('startDateFilter');
        this.endDateFilter = document.getElementById('endDateFilter');
        this.searchFilter = document.getElementById('searchFilter');
        this.filterForm = document.getElementById('filterForm');
        
        // Chart canvases
        this.ratingDistributionChart = document.getElementById('ratingDistributionChart');
        this.sentimentChart = document.getElementById('sentimentChart');
        
        // Global data
        this.data = window.feedbackData || {
            stats: {},
            filters: {},
            routes: {}
        };
    }

    bindEvents() {
        // Sidebar toggle - Fix the event listener
        if (this.menuToggleBtn) {
            // Remove any existing event listeners first
            this.menuToggleBtn.replaceWith(this.menuToggleBtn.cloneNode(true));
            
            // Get the fresh reference
            this.menuToggleBtn = document.getElementById('menu-toggle');
            
            // Add new event listener
            this.menuToggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                document.getElementById('wrapper').classList.toggle('toggled');
            });
        }

        // PDF generation
        if (this.generatePdfBtn) {
            this.generatePdfBtn.addEventListener('click', () => this.generatePDF());
        }

        // CSV export
        if (this.exportCsvBtn) {
            this.exportCsvBtn.addEventListener('click', () => this.exportCSV());
        }

        // Data refresh
        if (this.refreshDataBtn) {
            this.refreshDataBtn.addEventListener('click', () => this.refreshData());
        }

        // Auto-submit filters on change
        this.setupAutoSubmitFilters();
    }

    setupAutoSubmitFilters() {
        const autoSubmitElements = [
            this.ratingFilter,
            this.startDateFilter,
            this.endDateFilter
        ];

        autoSubmitElements.forEach(element => {
            if (element) {
                element.addEventListener('change', () => {
                    if (this.filterForm) {
                        this.filterForm.submit();
                    }
                });
            }
        });

        // Handle search filter with debounce
        if (this.searchFilter) {
            let searchTimeout;
            this.searchFilter.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.filterForm) {
                        this.filterForm.submit();
                    }
                }, 500); // 500ms debounce
            });
        }
    }

    updateFilterListeners() {
        // Ensure date range validation
        if (this.startDateFilter && this.endDateFilter) {
            this.startDateFilter.addEventListener('change', () => {
                if (this.startDateFilter.value && this.endDateFilter.value) {
                    if (this.startDateFilter.value > this.endDateFilter.value) {
                        this.endDateFilter.value = this.startDateFilter.value;
                    }
                }
            });

            this.endDateFilter.addEventListener('change', () => {
                if (this.startDateFilter.value && this.endDateFilter.value) {
                    if (this.endDateFilter.value < this.startDateFilter.value) {
                        this.startDateFilter.value = this.endDateFilter.value;
                    }
                }
            });
        }
    }

    initializeCharts() {
        this.createRatingDistributionChart();
        this.createSentimentChart();
    }

    createRatingDistributionChart() {
        if (!this.ratingDistributionChart) return;

        const ctx = this.ratingDistributionChart.getContext('2d');
        const stats = this.data.stats || {};
        
        // Get distribution from window.feedbackData or from stats
        const distribution = window.feedbackData?.rating_distribution || 
                            stats.rating_distribution || 
                            {5: 0, 4: 0, 3: 0, 2: 0, 1: 0};

        this.charts.ratingDistribution = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['5★', '4★', '3★', '2★', '1★'],
                datasets: [{
                    label: 'Number of Reviews',
                    data: [
                        distribution[5] || 0,
                        distribution[4] || 0,
                        distribution[3] || 0,
                        distribution[2] || 0,
                        distribution[1] || 0
                    ],
                    backgroundColor: [
                        '#A8DF8E', // 5 stars - Light Green
                        '#B0FFFA', // 4 stars - Light Teal/Cyan
                        '#FEEE91', // 3 stars - Light Yellow
                        '#FDACAC', // 2 stars - Light Pink/Red
                        '#FD7979'  // 1 star - Light Red
                    ],
                    borderColor: [
                        '#8BCA7A', // 5 stars - Darker Green border
                        '#8CE6E0', // 4 stars - Darker Teal border
                        '#F0DB70', // 3 stars - Darker Yellow border
                        '#E89999', // 2 stars - Darker Pink border
                        '#E56A6A'  // 1 star - Darker Red border
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Number of Reviews'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Star Rating'
                        }
                    }
                }
            }
        });
    }
    createSentimentChart() {
        if (!this.sentimentChart) return;

        const ctx = this.sentimentChart.getContext('2d');
        const stats = this.data.stats || {};

        // Make sure we have numbers, default to 0 if not
        const positive = parseInt(stats.positive_reviews) || 0;
        const neutral = parseInt(stats.neutral_reviews) || 0;
        const negative = parseInt(stats.negative_reviews) || 0;

        // Debug: Check sentiment data
        console.log('Sentiment data:', { positive, neutral, negative });

        // Check if we have any data
        if (positive === 0 && neutral === 0 && negative === 0) {
            console.warn('No sentiment data available');
            // Show a message or hide the chart
            this.sentimentChart.parentElement.innerHTML += `
                <div class="text-center text-muted mt-3">
                    <i class="fas fa-chart-pie fa-2x mb-2"></i>
                    <p>No sentiment data available</p>
                </div>
            `;
            return;
        }

        this.charts.sentiment = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Positive (4-5★)', 'Neutral (3★)', 'Negative (1-2★)'],
                datasets: [{
                    data: [positive, neutral, negative],
                    backgroundColor: [
                        '#A8DF8E', // Positive - Light Green
                        '#FEEE91', // Neutral - Light Yellow
                        '#FD7979'  // Negative - Light Red
                    ],
                    borderColor: [
                        '#8BCA7A', // Darker green border
                        '#F0DB70', // Darker yellow border
                        '#E56A6A'  // Darker red border
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = positive + neutral + negative;
                                const percentage = total > 0 ? 
                                    Math.round((context.raw / total) * 100) : 0;
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    generatePDF() {
        if (!this.data.routes.generatePdf) {
            console.error('PDF generation route not defined');
            this.showToast('error', 'PDF generation route not configured');
            return;
        }

        const form = this.filterForm;
        if (!form) {
            this.showToast('error', 'Filter form not found');
            return;
        }

        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData) {
            if (value) params.append(key, value);
        }
        
        const url = `${this.data.routes.generatePdf}?${params.toString()}`;
        
        // Show loading state
        const originalText = this.generatePdfBtn.innerHTML;
        this.generatePdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating...';
        this.generatePdfBtn.disabled = true;
        
        // Open PDF in a new tab for inline preview; fallback to iframe if popups are blocked
        let iframe = null;
        try {
            window.open(url, '_blank');
        } catch (e) {
            // Fallback to iframe download if popups are blocked
            iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;
            document.body.appendChild(iframe);
        }
        
        // Reset button after delay
        setTimeout(() => {
            this.generatePdfBtn.innerHTML = originalText;
            this.generatePdfBtn.disabled = false;
            
            // Remove iframe if it was created
            if (iframe && iframe.parentNode) {
                iframe.parentNode.removeChild(iframe);
            }
            
            this.showToast('success', 'PDF report is being generated. Check your downloads.');
        }, 3000);
    }

    exportCSV() {
        if (!this.data.routes.exportCsv) {
            console.error('CSV export route not defined');
            this.showToast('error', 'CSV export route not configured');
            return;
        }

        const form = this.filterForm;
        if (!form) {
            this.showToast('error', 'Filter form not found');
            return;
        }

        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData) {
            if (value) params.append(key, value);
        }
        
        const url = `${this.data.routes.exportCsv}?${params.toString()}`;
        
        // Show loading state
        const originalText = this.exportCsvBtn.innerHTML;
        this.exportCsvBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Exporting...';
        this.exportCsvBtn.disabled = true;
        
        // Create temporary iframe for download
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);
        
        // Reset button after delay
        setTimeout(() => {
            this.exportCsvBtn.innerHTML = originalText;
            this.exportCsvBtn.disabled = false;
            
            // Remove iframe
            if (iframe.parentNode) {
                iframe.parentNode.removeChild(iframe);
            }
            
            this.showToast('success', 'CSV export is being generated. Check your downloads.');
        }, 3000);
    }

    refreshData() {
        if (this.refreshDataBtn) {
            const originalText = this.refreshDataBtn.innerHTML;
            this.refreshDataBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Refreshing...';
            this.refreshDataBtn.disabled = true;
            
            // Reload page after a short delay to show loading state
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            window.location.reload();
        }
    }

    setupAutoRefresh() {
        // Auto-refresh data every 5 minutes (300000 milliseconds)
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.refreshData();
            }
        }, 300000);
    }

    showToast(type, message, title = 'Notification') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }

    updateCharts() {
        if (this.charts.ratingDistribution) {
            this.charts.ratingDistribution.destroy();
        }
        if (this.charts.sentiment) {
            this.charts.sentiment.destroy();
        }
        
        this.initializeCharts();
    }

    fetchChartData() {
        if (!this.data.routes.chartData) return;

        const form = this.filterForm;
        if (!form) return;

        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData) {
            if (value) params.append(key, value);
        }
        
        fetch(`${this.data.routes.chartData}?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateChartData(data);
                }
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
            });
    }

    updateChartData(data) {
        // Update charts with new data
        // This would be called if you want to update charts without refreshing the page
        console.log('Chart data updated:', data);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.feedbackApp = new FeedbackReports();
});

// Auto-close alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        setTimeout(() => {
            bsAlert.close();
        }, 5000);
    });
}, 5000);