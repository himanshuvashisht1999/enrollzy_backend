$(document).ready(function () {
    // Helper to get CSRF token
    const getToken = () => $('meta[name="csrf-token"]').attr('content');

    // Like Toggle
    $(document).on('click', '.like-btn', function () {
        let btn = $(this);
        let id = btn.data('id');
        let type = btn.data('type');

        $.ajax({
            url: "/community/interact/like",
            type: "POST",
            data: {
                _token: getToken(),
                id: id,
                type: type
            },
            success: function (res) {
                if (res.success) {
                    btn.find('.like-count').text(res.count);
                    if (res.liked) {
                        btn.addClass('active').css('color', 'var(--theme-one)');
                    } else {
                        btn.removeClass('active').css('color', '');
                    }
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    alert('Please login to like this!');
                } else if (xhr.status === 419) {
                    alert('Session expired. Please refresh the page.');
                } else {
                    alert('An error occurred. Status: ' + xhr.status);
                }
            }
        });
    });

    // Toggle Comment Box
    $(document).on('click', '.comment-toggle', function () {
        $(this).closest('.post-card').find('.add-comment').toggleClass('d-none');
    });

    // Store Reply
    $(document).on('click', '.add-comment-btn', function () {
        let btn = $(this);
        let container = btn.parent(); // More direct for input-group or parent container
        let textarea = container.find('.comment-input');

        // If btn is in an input-group, container is correct. 
        // If not, we might need to find the closest wrapper.
        if (container.hasClass('input-group')) {
            textarea = container.find('.comment-input');
        } else {
            container = btn.closest('.add-comment, .reply-form-wrap');
            textarea = container.find('.comment-input');
        }

        let content = textarea.val();
        let questionId = btn.data('question-id');
        let parentId = btn.data('parent-id') || null;

        if (!content) return;

        $.ajax({
            url: "/community/interact/reply",
            type: "POST",
            data: {
                _token: getToken(),
                question_id: questionId,
                parent_id: parentId,
                content: content
            },
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Error posting reply');
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    alert('Please login to comment!');
                } else if (xhr.status === 419) {
                    alert('Session expired. Please refresh the page.');
                } else {
                    alert('An error occurred. Status: ' + xhr.status);
                }
            }
        });
    });

    // Nested Reply Trigger
    $(document).on('click', '.reply-trigger', function () {
        $(this).closest('.comment-item').find('.reply-form-wrap').toggleClass('d-none');
    });
});

