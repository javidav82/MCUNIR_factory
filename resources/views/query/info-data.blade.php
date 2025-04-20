@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-database"></i> Query Info Data</h2>
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

@push('scripts')
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

        // Handle search form submission
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            loadData();
        });
    });

    function loadData(page = 1) {
        const search = $('#search').val();
        const status = $('#status').val();
        const dateRange = $('#dateRange').val();

        $.ajax({
            url: '/api/user/print-jobs',
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

    function updateTable(jobs) {
        const tbody = $('table tbody');
        tbody.empty();

        jobs.forEach(function(job) {
            tbody.append(`
                <tr>
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
            url: `/api/user/print-jobs/${jobId}`,
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
@endpush 