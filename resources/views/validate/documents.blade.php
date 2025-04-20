@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-check-circle"></i> Validate Info Documents</h2>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="searchForm" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" placeholder="Search by title or ID...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Validation Status</label>
                    <select class="form-select" id="status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="validated">Validated</option>
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

    <!-- Documents Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Submitted By</th>
                            <th>Submitted At</th>
                            <th>Last Updated</th>
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
</div>

<!-- Validation Modal -->
<div class="modal fade" id="validationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Validation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="validationForm">
                    <input type="hidden" id="documentId">
                    <div class="mb-3">
                        <label class="form-label">Document Title</label>
                        <input type="text" class="form-control" id="documentTitle" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Content</label>
                        <div class="border p-3 bg-light" id="documentContent"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Validation Status</label>
                        <select class="form-select" id="validationStatus" required>
                            <option value="">Select Status</option>
                            <option value="validated">Validated</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea class="form-control" id="feedback" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        // Handle validation submission
        $('#submitValidation').on('click', function() {
            submitValidation();
        });
    });

    function loadData(page = 1) {
        const search = $('#search').val();
        const status = $('#status').val();
        const dateRange = $('#dateRange').val();

        $.ajax({
            url: '/api/documents/validation',
            method: 'GET',
            data: {
                page: page,
                search: search,
                status: status,
                date_range: dateRange
            },
            success: function(response) {
                updateTable(response.data.documents);
                updatePagination(response.data.pagination);
            },
            error: function(xhr) {
                alert('Error loading data');
            }
        });
    }

    function updateTable(documents) {
        const tbody = $('table tbody');
        tbody.empty();

        documents.forEach(function(doc) {
            tbody.append(`
                <tr>
                    <td>${doc.id}</td>
                    <td>${doc.title}</td>
                    <td>
                        <span class="badge bg-${getStatusBadgeClass(doc.status)}">
                            ${doc.status}
                        </span>
                    </td>
                    <td>${doc.submitted_by}</td>
                    <td>${formatDate(doc.created_at)}</td>
                    <td>${formatDate(doc.updated_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewDocument(${doc.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="validateDocument(${doc.id})">
                            <i class="fas fa-check"></i>
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

    function viewDocument(id) {
        $.ajax({
            url: `/api/documents/${id}`,
            method: 'GET',
            success: function(response) {
                $('#documentId').val(id);
                $('#documentTitle').val(response.data.title);
                $('#documentContent').text(response.data.content);
                $('#validationModal').modal('show');
            },
            error: function(xhr) {
                alert('Error loading document');
            }
        });
    }

    function validateDocument(id) {
        viewDocument(id);
        $('#validationStatus').val('');
        $('#feedback').val('');
    }

    function submitValidation() {
        const id = $('#documentId').val();
        const status = $('#validationStatus').val();
        const feedback = $('#feedback').val();

        if (!status || !feedback) {
            alert('Please fill in all required fields');
            return;
        }

        $.ajax({
            url: `/api/documents/${id}/validate`,
            method: 'POST',
            data: {
                status: status,
                feedback: feedback
            },
            success: function(response) {
                $('#validationModal').modal('hide');
                loadData();
                alert('Document validation submitted successfully');
            },
            error: function(xhr) {
                alert('Error submitting validation');
            }
        });
    }

    function getStatusBadgeClass(status) {
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
</script>
@endpush 