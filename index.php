<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Listing & Rating System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/raty/2.7.1/jquery.raty.min.css" rel="stylesheet">
    <style>
        .raty-readonly { cursor: pointer; }
        .raty-readonly .raty-cancel { display: none !important; }
        #businessTable tbody tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Business Listing & Rating System</h1>

        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="btnAddBusiness" data-bs-toggle="modal" data-bs-target="#businessModal">
                + Add Business
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="businessTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Business Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                        <th>Average Rating</th>
                    </tr>
                </thead>
                <tbody id="businessTableBody">
                    <tr><td colspan="7" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Business Add/Edit Modal -->
    <div class="modal fade" id="businessModal" tabindex="-1" aria-labelledby="businessModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="businessModalLabel">Add Business</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="businessForm">
                        <input type="hidden" id="businessId" name="id">
                        <div class="mb-3">
                            <label for="businessName" class="form-label">Business Name *</label>
                            <input type="text" class="form-control" id="businessName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="businessAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="businessAddress" name="address" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="businessPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="businessPhone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="businessEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="businessEmail" name="email">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnSaveBusiness">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModalLabel">Rate this Business</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2">People who rated this business</h6>
                        <div id="ratingListContainer" class="small">
                            <div id="ratingListLoading" class="text-muted">Loading...</div>
                            <ul id="ratingList" class="list-group list-group-flush" style="display:none;"></ul>
                            <div id="ratingListEmpty" class="text-muted" style="display:none;">No ratings yet. Be the first to rate!</div>
                        </div>
                    </div>
                    <h6 class="border-bottom pb-2">Add your rating</h6>
                    <form id="ratingForm">
                        <input type="hidden" id="ratingBusinessId" name="business_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ratingName" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="ratingName" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ratingEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="ratingEmail" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ratingPhone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="ratingPhone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating (0-5, half stars) *</label>
                                <div id="ratingStars"></div>
                                <input type="hidden" id="ratingValue" name="rating" value="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitRating">Submit Rating</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raty/2.7.1/jquery.raty.min.js"></script>
    <script>
    $(function() {
        const strRatyPath = 'https://cdnjs.cloudflare.com/ajax/libs/raty/2.7.1/images/';

        function loadBusinesses() {
            $.get('api/businesses.php?action=list')
                .done(function(objRes) {
                    if (objRes.success && Array.isArray(objRes.data)) {
                        renderTable(objRes.data);
                    } else {
                        $('#businessTableBody').html('<tr><td colspan="7" class="text-danger">Failed to load businesses</td></tr>');
                    }
                })
                .fail(function() {
                    $('#businessTableBody').html('<tr><td colspan="7" class="text-danger">Error loading businesses</td></tr>');
                });
        }

        function renderTable(arrBusinesses) {
            if (!arrBusinesses || arrBusinesses.length === 0) {
                $('#businessTableBody').html('<tr><td colspan="7" class="text-center">No businesses found. Add one to get started.</td></tr>');
                return;
            }

            let strHtml = '';
            arrBusinesses.forEach(function(objB) {
                const floatAvg = parseFloat(objB.avg_rating || 0);
                strHtml += '<tr data-id="' + objB.id + '">' +
                    '<td>' + objB.id + '</td>' +
                    '<td>' + escapeHtml(objB.name) + '</td>' +
                    '<td>' + escapeHtml(objB.address || '') + '</td>' +
                    '<td>' + escapeHtml(objB.phone || '') + '</td>' +
                    '<td>' + escapeHtml(objB.email || '') + '</td>' +
                    '<td>' +
                    '<button class="btn btn-sm btn-outline-primary me-1 btn-edit" data-id="' + objB.id + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-success me-1 btn-rate" data-id="' + objB.id + '" data-name="' + escapeHtml(objB.name) + '">Rate this</button>' +
                    '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + objB.id + '">Delete</button>' +
                    '</td>' +
                    '<td><div class="raty-readonly star-' + objB.id + '" data-id="' + objB.id + '" data-score="' + floatAvg + '" data-readonly="true" title="Click to rate"></div></td>' +
                    '</tr>';
            });
            $('#businessTableBody').html(strHtml);

            $('.raty-readonly').each(function() {
                const $el = $(this);
                if ($el.data('raty-inited')) return;
                $el.raty({
                    path: strRatyPath,
                    readOnly: true,
                    score: parseFloat($el.data('score')) || 0,
                    half: true,
                    halfShow: true,
                    noRatedMsg: 'Not rated yet!'
                });
                $el.data('raty-inited', true);
            });
        }

        function escapeHtml(strText) {
            const objDiv = document.createElement('div');
            objDiv.textContent = strText;
            return objDiv.innerHTML;
        }

        $('#btnAddBusiness').on('click', function() {
            $('#businessModalLabel').text('Add Business');
            $('#businessForm')[0].reset();
            $('#businessId').val('');
        });

        $(document).on('click', '.btn-edit', function() {
            const intId = $(this).data('id');
            $.get('api/businesses.php?action=get&id=' + intId)
                .done(function(objRes) {
                    if (objRes.success && objRes.data) {
                        const objB = objRes.data;
                        $('#businessModalLabel').text('Edit Business');
                        $('#businessId').val(objB.id);
                        $('#businessName').val(objB.name);
                        $('#businessAddress').val(objB.address || '');
                        $('#businessPhone').val(objB.phone || '');
                        $('#businessEmail').val(objB.email || '');
                        new bootstrap.Modal($('#businessModal')[0]).show();
                    }
                });
        });

        $('#btnSaveBusiness').on('click', function() {
            const strId = $('#businessId').val();
            const objData = {
                action: strId ? 'update' : 'create',
                id: strId || undefined,
                name: $('#businessName').val().trim(),
                address: $('#businessAddress').val().trim(),
                phone: $('#businessPhone').val().trim(),
                email: $('#businessEmail').val().trim()
            };
            if (!objData.name) {
                alert('Business name is required');
                return;
            }

            $.post('api/businesses.php', objData)
                .done(function(objRes) {
                    if (objRes.success) {
                        bootstrap.Modal.getInstance($('#businessModal')[0]).hide();
                        loadBusinesses();
                    } else {
                        alert(objRes.message || 'Error saving business');
                    }
                })
                .fail(function() {
                    alert('Error saving business');
                });
        });

        $(document).on('click', '.btn-delete', function() {
            if (!confirm('Are you sure you want to delete this business?')) return;
            const intId = $(this).data('id');
            $.post('api/businesses.php', { action: 'delete', id: intId })
                .done(function(objRes) {
                    if (objRes.success) {
                        $('tr[data-id="' + intId + '"]').fadeOut(300, function() { $(this).remove(); });
                        const $tbody = $('#businessTableBody');
                        if ($tbody.find('tr').length === 0) {
                            $tbody.html('<tr><td colspan="7" class="text-center">No businesses found.</td></tr>');
                        }
                    } else {
                        alert(objRes.message || 'Error deleting business');
                    }
                })
                .fail(function() {
                    alert('Error deleting business');
                });
        });

        function loadRatingList(intBusinessId) {
            $('#ratingList').hide().empty();
            $('#ratingListEmpty').hide();
            $('#ratingListLoading').show();
            $.get('api/ratings.php?business_id=' + intBusinessId)
                .done(function(objRes) {
                    $('#ratingListLoading').hide();
                    if (objRes.success && Array.isArray(objRes.data)) {
                        const arrRatings = objRes.data;
                        if (arrRatings.length === 0) {
                            $('#ratingListEmpty').show();
                        } else {
                            arrRatings.forEach(function(objR) {
                                const strDate = objR.created_at ? new Date(objR.created_at).toLocaleDateString() : '-';
                                const strItem = '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                                    '<span>' + escapeHtml(objR.name) + ' <span class="text-muted">(' + objR.rating + ' &#9733;)</span></span>' +
                                    '<small class="text-muted">' + strDate + '</small></li>';
                                $('#ratingList').append(strItem);
                            });
                            $('#ratingList').show();
                        }
                    }
                })
                .fail(function() {
                    $('#ratingListLoading').hide();
                    $('#ratingListEmpty').text('Failed to load ratings.').show();
                });
        }

        function openRatingModal(intBusinessId) {
            if (!intBusinessId) return;
            const strBusinessName = $('.btn-rate[data-id="' + intBusinessId + '"]').data('name') || 'this Business';
            $('#ratingModalLabel').text('Rate: ' + strBusinessName);
            $('#ratingBusinessId').val(intBusinessId);
            $('#ratingForm')[0].reset();
            $('#ratingBusinessId').val(intBusinessId);
            $('#ratingValue').val(0);

            $('#ratingStars').empty();
            $('#ratingStars').raty({
                path: strRatyPath,
                half: true,
                score: 0,
                click: function(intScore) {
                    $('#ratingValue').val(intScore);
                }
            });

            loadRatingList(intBusinessId);
            new bootstrap.Modal($('#ratingModal')[0]).show();
        }

        $(document).on('click', '.btn-rate', function() {
            const intId = $(this).data('id');
            openRatingModal(intId);
        });

        $(document).on('click', '.raty-readonly', function() {
            const intId = $(this).data('id');
            openRatingModal(intId);
        });

        $('#btnSubmitRating').on('click', function() {
            const intBusinessId = $('#ratingBusinessId').val();
            const strName = $('#ratingName').val().trim();
            const strEmail = $('#ratingEmail').val().trim();
            const strPhone = $('#ratingPhone').val().trim();
            const mixedRating = $('#ratingStars').raty('score');

            if (!strName || !strEmail) {
                alert('Name and Email are required');
                return;
            }
            const floatRatingVal = (mixedRating !== null && mixedRating !== undefined) ? parseFloat(mixedRating) : null;
            if (floatRatingVal === null || floatRatingVal < 0 || floatRatingVal > 5) {
                alert('Please select a rating (1-5 stars)');
                return;
            }

            $.ajax({
                url: 'api/ratings.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    business_id: intBusinessId,
                    name: strName,
                    email: strEmail,
                    phone: strPhone,
                    rating: floatRatingVal
                })
            })
            .done(function(objRes) {
                if (objRes.success) {
                    loadRatingList(intBusinessId);
                    const $row = $('tr[data-id="' + intBusinessId + '"]');
                    const $starCell = $row.find('.raty-readonly');
                    const floatNewAvg = objRes.data.avg_rating;
                    $starCell.raty('score', floatNewAvg);
                    $starCell.data('score', floatNewAvg);
                } else {
                    alert(objRes.message || 'Error submitting rating');
                }
            })
            .fail(function() {
                alert('Error submitting rating');
            });
        });

        loadBusinesses();
    });
    </script>
</body>
</html>
