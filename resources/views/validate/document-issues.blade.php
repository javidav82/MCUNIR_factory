@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-exclamation-triangle"></i> Validate Document Issues</h2>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loadIssueModal">
                <i class="fas fa-plus"></i> Load Document Issue
            </button>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="searchForm" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" placeholder="Search by job ID, title or issue...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Issue Status</label>
                    <select class="form-select" id="status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="reviewing">Reviewing</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
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

    <!-- Issues Table -->
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
                            <th>Issue Type</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Reported At</th>
                            <th>Last Updated</th>
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
                <button class="btn btn-primary" id="validateIssuesBtn" disabled>
                    <i class="fas fa-check-circle"></i> Validate Issues
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

    <!-- Issue Details Accordion -->
    <div class="accordion mt-4" id="issueDetailsAccordion">
        <!-- Issue details will be loaded here via JavaScript -->
    </div>
</div>

<!-- Load Issue Modal -->
<div class="modal fade" id="loadIssueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Load Document Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loadIssueForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Job ID</label>
                                <input type="text" class="form-control" id="jobId" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" id="issueTitle" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Issue Type</label>
                                <select class="form-select" id="issueType" required>
                                    <option value="">Select Type</option>
                                    <option value="format">Format</option>
                                    <option value="content">Content</option>
                                    <option value="quality">Quality</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" id="issuePriority" required>
                                    <option value="">Select Priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Document Type</label>
                                <input type="text" class="form-control" id="documentType" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pages</label>
                                <input type="number" class="form-control" id="pages" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Format</label>
                                <input type="text" class="form-control" id="format" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Document File</label>
                                <input type="file" class="form-control" id="documentFile" accept=".pdf,.doc,.docx" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issue Description</label>
                        <textarea class="form-control" id="issueDescription" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitLoadIssue">Load Issue</button>
            </div>
        </div>
    </div>
</div>

<!-- Validate Issues Modal -->
<div class="modal fade" id="validateIssuesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validate Document Issues</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="validateIssuesForm">
                    <div class="mb-3">
                        <label class="form-label">Validation Status</label>
                        <select class="form-select" id="validationStatus" required>
                            <option value="">Select Status</option>
                            <option value="reviewing">Reviewing</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes</label>
                        <textarea class="form-control" id="resolutionNotes" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Action Taken</label>
                        <select class="form-select" id="actionTaken" required>
                            <option value="">Select Action</option>
                            <option value="fixed">Fixed</option>
                            <option value="ignored">Ignored</option>
                            <option value="reported">Reported to Support</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Comments</label>
                        <textarea class="form-control" id="additionalComments" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitValidation">Submit Validation</button>
            </div>
        </div>
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

        // Handle select all checkbox
        $('#selectAll').on('change', function() {
            $('.issue-checkbox').prop('checked', $(this).prop('checked'));
            updateBulkActionButton();
        });

        // Handle individual checkboxes
        $(document).on('change', '.issue-checkbox', function() {
            updateBulkActionButton();
            updateSelectAllCheckbox();
        });

        // Handle validate issues button click
        $('#validateIssuesBtn').on('click', function() {
            $('#validateIssuesModal').modal('show');
        });

        // Handle validation submission
        $('#submitValidation').on('click', function() {
            submitValidation();
        });

        // Handle load issue submission
        $('#submitLoadIssue').on('click', function() {
            submitLoadIssue();
        });

        // Reset form when modal is closed
        $('#loadIssueModal').on('hidden.bs.modal', function() {
            $('#loadIssueForm')[0].reset();
        });
    });

    function loadData(page = 1) {
        const search = $('#search').val();
        const status = $('#status').val();
        const dateRange = $('#dateRange').val();

        $.ajax({
            url: '/api/document-issues',
            method: 'GET',
            data: {
                page: page,
                search: search,
                status: status,
                date_range: dateRange
            },
            success: function(response) {
                updateTable(response.data.issues);
                updatePagination(response.data.pagination);
            },
            error: function(xhr) {
                alert('Error loading data');
            }
        });
    }

    function updateTable(issues) {
        const tbody = $('table tbody');
        tbody.empty();

        issues.forEach(function(issue) {
            tbody.append(`
                <tr onclick="viewIssueDetails(${issue.id})" style="cursor: pointer;">
                    <td onclick="event.stopPropagation();">
                        <div class="form-check">
                            <input class="form-check-input issue-checkbox" type="checkbox" value="${issue.id}">
                        </div>
                    </td>
                    <td>${issue.job_id}</td>
                    <td>${issue.title}</td>
                    <td>
                        <span class="badge bg-${getIssueTypeBadgeClass(issue.issue_type)}">
                            ${issue.issue_type}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${getStatusBadgeClass(issue.status)}">
                            ${issue.status}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${getPriorityBadgeClass(issue.priority)}">
                            ${issue.priority}
                        </span>
                    </td>
                    <td>${formatDate(issue.reported_at)}</td>
                    <td>${formatDate(issue.updated_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="event.stopPropagation(); viewIssueDetails(${issue.id})">
                            <i class="fas fa-eye"></i> View
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

    function viewIssueDetails(issueId) {
        $.ajax({
            url: `/api/document-issues/${issueId}`,
            method: 'GET',
            success: function(response) {
                const issue = response.data;
                const accordion = $('#issueDetailsAccordion');
                
                // Clear existing accordion items
                accordion.empty();

                // Add new accordion item
                accordion.append(`
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading${issue.id}">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse${issue.id}" aria-expanded="true" 
                                    aria-controls="collapse${issue.id}">
                                Issue Details - ${issue.title}
                            </button>
                        </h2>
                        <div id="collapse${issue.id}" class="accordion-collapse collapse show" 
                             aria-labelledby="heading${issue.id}" data-bs-parent="#issueDetailsAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Issue Information</h5>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Issue ID:</th>
                                                <td>${issue.id}</td>
                                            </tr>
                                            <tr>
                                                <th>Job ID:</th>
                                                <td>${issue.job_id}</td>
                                            </tr>
                                            <tr>
                                                <th>Title:</th>
                                                <td>${issue.title}</td>
                                            </tr>
                                            <tr>
                                                <th>Type:</th>
                                                <td>
                                                    <span class="badge bg-${getIssueTypeBadgeClass(issue.issue_type)}">
                                                        ${issue.issue_type}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <span class="badge bg-${getStatusBadgeClass(issue.status)}">
                                                        ${issue.status}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Priority:</th>
                                                <td>
                                                    <span class="badge bg-${getPriorityBadgeClass(issue.priority)}">
                                                        ${issue.priority}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Document Information</h5>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Document Type:</th>
                                                <td>${issue.document_type}</td>
                                            </tr>
                                            <tr>
                                                <th>Pages:</th>
                                                <td>${issue.pages}</td>
                                            </tr>
                                            <tr>
                                                <th>Format:</th>
                                                <td>${issue.format}</td>
                                            </tr>
                                            <tr>
                                                <th>Reported At:</th>
                                                <td>${formatDateTime(issue.reported_at)}</td>
                                            </tr>
                                            <tr>
                                                <th>Last Updated:</th>
                                                <td>${formatDateTime(issue.updated_at)}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5>Issue Description</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-0">${issue.description}</p>
                                        </div>
                                    </div>
                                </div>
                                ${issue.resolution_notes ? `
                                <div class="mt-3">
                                    <h5>Resolution Notes</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-0">${issue.resolution_notes}</p>
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
                alert('Error loading issue details');
            }
        });
    }

    function updateBulkActionButton() {
        const checkedCount = $('.issue-checkbox:checked').length;
        $('#validateIssuesBtn').prop('disabled', checkedCount === 0);
    }

    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.issue-checkbox').length;
        const checkedCount = $('.issue-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCount);
    }

    function submitValidation() {
        const selectedIssues = $('.issue-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const validationStatus = $('#validationStatus').val();
        const resolutionNotes = $('#resolutionNotes').val();
        const actionTaken = $('#actionTaken').val();
        const additionalComments = $('#additionalComments').val();

        if (!validationStatus || !resolutionNotes || !actionTaken) {
            alert('Please fill in all required fields');
            return;
        }

        $.ajax({
            url: '/api/document-issues/validate',
            method: 'POST',
            data: {
                issue_ids: selectedIssues,
                status: validationStatus,
                resolution_notes: resolutionNotes,
                action_taken: actionTaken,
                additional_comments: additionalComments
            },
            success: function(response) {
                $('#validateIssuesModal').modal('hide');
                loadData();
                alert('Issues validated successfully');
            },
            error: function(xhr) {
                alert('Error validating issues');
            }
        });
    }

    function submitLoadIssue() {
        const formData = new FormData();
        formData.append('job_id', $('#jobId').val());
        formData.append('title', $('#issueTitle').val());
        formData.append('issue_type', $('#issueType').val());
        formData.append('priority', $('#issuePriority').val());
        formData.append('document_type', $('#documentType').val());
        formData.append('pages', $('#pages').val());
        formData.append('format', $('#format').val());
        formData.append('description', $('#issueDescription').val());
        formData.append('document_file', $('#documentFile')[0].files[0]);

        // Validate required fields
        if (!formData.get('job_id') || !formData.get('title') || !formData.get('issue_type') || 
            !formData.get('priority') || !formData.get('document_type') || !formData.get('pages') || 
            !formData.get('format') || !formData.get('description') || !formData.get('document_file')) {
            alert('Please fill in all required fields');
            return;
        }

        $.ajax({
            url: '/api/document-issues/load',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#loadIssueModal').modal('hide');
                loadData();
                alert('Document issue loaded successfully');
            },
            error: function(xhr) {
                alert('Error loading document issue');
            }
        });
    }

    function getIssueTypeBadgeClass(type) {
        const classes = {
            'format': 'info',
            'content': 'warning',
            'quality': 'danger',
            'other': 'secondary'
        };
        return classes[type] || 'secondary';
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'pending': 'warning',
            'reviewing': 'info',
            'resolved': 'success',
            'rejected': 'danger'
        };
        return classes[status] || 'secondary';
    }

    function getPriorityBadgeClass(priority) {
        const classes = {
            'low': 'success',
            'medium': 'warning',
            'high': 'danger',
            'critical': 'dark'
        };
        return classes[priority] || 'secondary';
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    function formatDateTime(dateString) {
        return new Date(dateString).toLocaleString();
    }
</script>
@endpush 