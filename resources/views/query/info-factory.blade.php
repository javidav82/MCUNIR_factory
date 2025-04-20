@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-industry"></i> Query Info Factory</h2>
        </div>
    </div>

    <!-- Factory Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Print Jobs</h5>
                    <h2 class="card-text" id="totalJobs">0</h2>
                    <p class="card-text"><small>Last 30 days</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Completed Jobs</h5>
                    <h2 class="card-text" id="completedJobs">0</h2>
                    <p class="card-text"><small>Last 30 days</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Jobs</h5>
                    <h2 class="card-text" id="pendingJobs">0</h2>
                    <p class="card-text"><small>Current</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Failed Jobs</h5>
                    <h2 class="card-text" id="failedJobs">0</h2>
                    <p class="card-text"><small>Last 30 days</small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="searchForm" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" placeholder="Search by job ID or title...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Job Status</label>
                    <select class="form-select" id="status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateRange" class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>Job ID</th>
                            <th>Title</th>
                            <th>Job Status</th>
                            <th>Document Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Bulk Actions -->
            <div class="mt-3">
                <button class="btn btn-primary" id="updateStatusBtn" disabled>
                    <i class="fas fa-sync"></i> Update Status
                </button>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Pagination will be loaded here via JavaScript -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Job Details Accordion -->
    <div class="accordion mt-4" id="jobDetailsAccordion">
        <!-- Job details will be loaded here via JavaScript -->
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Job Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select" id="newStatus" required>
                            <option value="">Select Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="statusNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitStatusUpdate">Update Status</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#dateRange').daterangepicker({
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        // Load initial data
        loadData();
        loadFactoryData();
        loadPerformanceData();

        // Handle search form submission
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            loadData();
        });

        // Handle select all checkbox
        $('#selectAll').on('change', function() {
            $('.job-checkbox').prop('checked', $(this).prop('checked'));
            updateBulkActionButton();
        });

        // Handle individual checkboxes
        $(document).on('change', '.job-checkbox', function() {
            updateBulkActionButton();
            updateSelectAllCheckbox();
        });

        // Handle update status button click
        $('#updateStatusBtn').on('click', function() {
            $('#updateStatusModal').modal('show');
        });

        // Handle status update submission
        $('#submitStatusUpdate').on('click', function() {
            submitStatusUpdate();
        });

        // Refresh data every 5 minutes
        setInterval(loadFactoryData, 300000);
    });

    function loadData(page = 1) {
        const search = $('#search').val();
        const status = $('#status').val();
        const dateRange = $('#dateRange').val();

        $.ajax({
            url: '/api/factory/jobs',
            method: 'GET',
            data: {
                page: page,
                search: search,
                status: status,
                date_range: dateRange
            },
            success: function(response) {
                updateTable(response.data.jobs);
                updatePagination(response.data.pagination);
            },
            error: function(xhr) {
                alert('Error loading data');
            }
        });
    }

    function loadFactoryData() {
        $.ajax({
            url: '/api/factory/status',
            method: 'GET',
            success: function(response) {
                updateFactoryStats(response.data.stats);
                updateFactoryDetails(response.data.details);
                updateActivityTimeline(response.data.activities);
            },
            error: function(xhr) {
                console.error('Error loading factory data');
            }
        });
    }

    function loadPerformanceData() {
        $.ajax({
            url: '/api/factory/performance',
            method: 'GET',
            success: function(response) {
                createPerformanceChart(response.data);
            },
            error: function(xhr) {
                console.error('Error loading performance data');
            }
        });
    }

    function updateTable(jobs) {
        const tbody = $('table tbody');
        tbody.empty();

        jobs.forEach(function(job) {
            tbody.append(`
                <tr>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input job-checkbox" type="checkbox" value="${job.id}">
                        </div>
                    </td>
                    <td>${job.id}</td>
                    <td>${job.title}</td>
                    <td>
                        <span class="badge bg-${getJobStatusBadgeClass(job.job_status)}">
                            ${job.job_status}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${getDocumentStatusBadgeClass(job.document_status)}">
                            ${job.document_status}
                        </span>
                    </td>
                    <td>${formatDate(job.created_at)}</td>
                    <td>${formatDate(job.updated_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewJobDetails(${job.id})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </td>
                </tr>
            `);
        });
    }

    function updatePagination(pagination) {
        const paginationUl = $('.pagination');
        paginationUl.empty();

        // Previous button
        paginationUl.append(`
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadData(${pagination.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `);

        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            paginationUl.append(`
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadData(${i})">${i}</a>
                </li>
            `);
        }

        // Next button
        paginationUl.append(`
            <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadData(${pagination.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `);
    }

    function viewJobDetails(jobId) {
        $.ajax({
            url: `/api/factory/jobs/${jobId}`,
            method: 'GET',
            success: function(response) {
                const job = response.data;
                const accordion = $('#jobDetailsAccordion');
                
                // Clear existing accordion items
                accordion.empty();

                // Add new accordion item
                accordion.append(`
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading${job.id}">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse${job.id}" aria-expanded="true" 
                                    aria-controls="collapse${job.id}">
                                Job Details - ${job.title}
                            </button>
                        </h2>
                        <div id="collapse${job.id}" class="accordion-collapse collapse show" 
                             aria-labelledby="heading${job.id}" data-bs-parent="#jobDetailsAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Job Information</h5>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Job ID:</th>
                                                <td>${job.id}</td>
                                            </tr>
                                            <tr>
                                                <th>Title:</th>
                                                <td>${job.title}</td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <span class="badge bg-${getJobStatusBadgeClass(job.job_status)}">
                                                        ${job.job_status}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Created At:</th>
                                                <td>${formatDateTime(job.created_at)}</td>
                                            </tr>
                                            <tr>
                                                <th>Updated At:</th>
                                                <td>${formatDateTime(job.updated_at)}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Document Information</h5>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Document Status:</th>
                                                <td>
                                                    <span class="badge bg-${getDocumentStatusBadgeClass(job.document_status)}">
                                                        ${job.document_status}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Document Type:</th>
                                                <td>${job.document_type}</td>
                                            </tr>
                                            <tr>
                                                <th>Pages:</th>
                                                <td>${job.pages}</td>
                                            </tr>
                                            <tr>
                                                <th>Format:</th>
                                                <td>${job.format}</td>
                                            </tr>
                                            <tr>
                                                <th>Last Validation:</th>
                                                <td>${formatDateTime(job.last_validation)}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                ${job.feedback ? `
                                <div class="mt-3">
                                    <h5>Feedback</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-0">${job.feedback}</p>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                alert('Error loading job details');
            }
        });
    }

    function updateBulkActionButton() {
        const checkedCount = $('.job-checkbox:checked').length;
        $('#updateStatusBtn').prop('disabled', checkedCount === 0);
    }

    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.job-checkbox').length;
        const checkedCount = $('.job-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCount);
    }

    function submitStatusUpdate() {
        const selectedJobs = $('.job-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const newStatus = $('#newStatus').val();
        const notes = $('#statusNotes').val();

        if (!newStatus) {
            alert('Please select a status');
            return;
        }

        $.ajax({
            url: '/api/factory/jobs/update-status',
            method: 'POST',
            data: {
                job_ids: selectedJobs,
                status: newStatus,
                notes: notes
            },
            success: function(response) {
                $('#updateStatusModal').modal('hide');
                loadData();
                loadFactoryData();
                alert('Status updated successfully');
            },
            error: function(xhr) {
                alert('Error updating status');
            }
        });
    }

    function updateFactoryStats(stats) {
        $('#totalJobs').text(stats.total_jobs);
        $('#completedJobs').text(stats.completed_jobs);
        $('#pendingJobs').text(stats.pending_jobs);
        $('#failedJobs').text(stats.failed_jobs);
    }

    function updateFactoryDetails(details) {
        $('#factoryName').text(details.name);
        $('#factoryStatus').text(details.status)
            .removeClass('bg-success bg-danger')
            .addClass(details.status === 'Active' ? 'bg-success' : 'bg-danger');
        $('#lastMaintenance').text(formatDate(details.last_maintenance));
        $('#nextMaintenance').text(formatDate(details.next_maintenance));
    }

    function updateActivityTimeline(activities) {
        const timeline = $('#activityTimeline');
        timeline.empty();

        activities.forEach(function(activity) {
            timeline.append(`
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">${activity.title}</h6>
                        <p class="mb-0">${activity.description}</p>
                        <small class="text-muted">${formatDateTime(activity.timestamp)}</small>
                    </div>
                </div>
            `);
        });
    }

    function createPerformanceChart(data) {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Jobs Completed',
                    data: data.completed_jobs,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Jobs Failed',
                    data: data.failed_jobs,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Job Performance Over Time'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function getJobStatusBadgeClass(status) {
        const classes = {
            'pending': 'warning',
            'processing': 'info',
            'completed': 'success',
            'failed': 'danger'
        };
        return classes[status] || 'secondary';
    }

    function getDocumentStatusBadgeClass(status) {
        const classes = {
            'pending': 'warning',
            'validated': 'success',
            'rejected': 'danger'
        };
        return classes[status] || 'secondary';
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    function formatDateTime(dateString) {
        return new Date(dateString).toLocaleString();
    }
</script>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -20px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #007bff;
}

.timeline-content {
    padding-left: 20px;
    border-left: 2px solid #dee2e6;
}
</style>
@endpush 