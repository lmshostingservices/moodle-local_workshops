// AMD module for local_workshops — Workshop Scheduler
// Format: AMD define() — required for Moodle 4.x

define(['jquery'], function($) {
    'use strict';

    var workshopId = 0;
    var sesskey    = '';

    /**
     * Show a temporary toast notification.
     */
    function showToast(message, type) {
        var cls = 'lw-toast';
        if (type === 'error') cls += ' lw-toast-error';
        if (type === 'info')  cls += ' lw-toast-info';
        var toast = $('<div class="' + cls + '"></div>').text(message);
        $('body').append(toast);
        setTimeout(function() {
            toast.fadeOut(300, function() { toast.remove(); });
        }, 3000);
    }

    /**
     * Send AJAX request to ajax.php
     */
    function ajaxRequest(data, onSuccess, onError) {
        data.sesskey = sesskey;
        $.ajax({
            url: M.cfg.wwwroot + '/local/workshops/ajax.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    if (onSuccess) onSuccess(response);
                } else {
                    var msg = (response && response.error) ? response.error : 'An error occurred.';
                    if (onError) onError(msg);
                    else showToast(msg, 'error');
                }
            },
            error: function(xhr) {
                var msg = 'Request failed.';
                try { var r = JSON.parse(xhr.responseText); if (r.error) msg = r.error; } catch(e){}
                if (onError) onError(msg);
                else showToast(msg, 'error');
            }
        });
    }

    /**
     * Initialise the manage.php page (delete confirmations).
     */
    function initManagePage() {
        $(document).on('click', '.lw-confirm-delete', function(e) {
            var msg = $(this).data('confirm') || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
                return false;
            }
        });
    }

    /**
     * Initialise the view.php page.
     */
    function initViewPage(wid, sk) {
        workshopId = wid;
        sesskey    = sk;

        // ----- PARTICIPANTS -----
        initParticipantModal();
        initGroupEnrolModal();
        initAttendanceModal();

        // Remove participant
        $(document).on('click', '.lw-remove-participant', function() {
            if (!confirm('Remove this participant?')) return;
            var pid = $(this).data('id');
            var row = $(this).closest('tr');
            ajaxRequest({action: 'remove_participant', participantid: pid}, function() {
                row.fadeOut(300, function() { row.remove(); });
                showToast('Participant removed.');
            });
        });

        // Edit participant — populate modal
        $(document).on('click', '.lw-edit-participant', function() {
            var btn = $(this);
            $('#lw-modal-title').text('Edit Participant');
            $('#lw-participant-id').val(btn.data('id'));
            $('#lw-p-firstname').val(btn.data('firstname'));
            $('#lw-p-lastname').val(btn.data('lastname'));
            $('#lw-p-email').val(btn.data('email'));
            $('#lw-p-org').val(btn.data('org'));
            $('#lw-p-status').val(btn.data('status'));
            $('#lw-p-notes').val('');
            $('#lw-participant-modal').show();
        });

        // ----- TASKS -----
        $(document).on('change', '.lw-task-toggle', function() {
            var taskid  = $(this).data('taskid');
            var checked = $(this).is(':checked') ? 1 : 0;
            var item    = $(this).closest('.lw-task-item');
            ajaxRequest({action: 'toggle_task', taskid: taskid, checked: checked}, function(resp) {
                if (resp.status === 'Complete') {
                    item.addClass('lw-task-complete');
                    item.find('.lw-badge').removeClass('lw-badge-draft lw-badge-scheduled').addClass('lw-badge-completed').text('Complete');
                } else {
                    item.removeClass('lw-task-complete');
                    item.find('.lw-badge').removeClass('lw-badge-completed lw-badge-scheduled').addClass('lw-badge-draft').text('Pending');
                }
                updateProgressBar();
            });
        });

        // Delete task
        $(document).on('click', '.lw-delete-task', function() {
            if (!confirm('Remove this task?')) return;
            var taskid = $(this).data('id');
            var item   = $(this).closest('.lw-task-item');
            ajaxRequest({action: 'delete_task', taskid: taskid}, function() {
                item.fadeOut(300, function() { item.remove(); updateProgressBar(); });
                showToast('Task removed.');
            });
        });

        // Add task modal
        $('#lw-btn-add-task').on('click', function() {
            $('#lw-task-modal').show();
        });
        $(document).on('click', '.lw-modal-close', function() {
            $(this).closest('.lw-modal').hide();
        });

        // Submit add task form
        $('#lw-task-form').on('submit', function(e) {
            e.preventDefault();
            var formdata = $(this).serializeArray();
            var data = {action: 'add_task', workshopid: workshopId};
            $.each(formdata, function(i, f) { data[f.name] = f.value; });
            ajaxRequest(data, function() {
                showToast('Task added. Reloading...', 'info');
                setTimeout(function() { location.reload(); }, 800);
            });
        });

        // ----- DOCUMENTS -----
        // Intercept upload form — submit via XHR so ajax.php returns JSON (not a redirect).
        // ajax.php has AJAX_SCRIPT=true which forbids redirect(); this keeps the pattern consistent.
        $('#lw-upload-form').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            var submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Uploading...');

            var formData = new FormData(form);
            formData.append('sesskey', sesskey);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', M.cfg.wwwroot + '/local/workshops/ajax.php', true);
            xhr.onload = function() {
                submitBtn.prop('disabled', false).text('Upload');
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp && resp.success) {
                        showToast(resp.message || 'Document uploaded.', 'info');
                        setTimeout(function() { location.reload(); }, 800);
                    } else {
                        var msg = (resp && resp.error) ? resp.error : 'Upload failed.';
                        showToast(msg, 'error');
                    }
                } catch(ex) {
                    showToast('Upload failed.', 'error');
                }
            };
            xhr.onerror = function() {
                submitBtn.prop('disabled', false).text('Upload');
                showToast('Upload failed.', 'error');
            };
            xhr.send(formData);
        });

        $(document).on('click', '.lw-delete-doc', function() {
            if (!confirm('Delete this document?')) return;
            var docid = $(this).data('id');
            var row   = $(this).closest('tr');
            ajaxRequest({action: 'delete_document', docid: docid}, function() {
                row.fadeOut(300, function() { row.remove(); });
                showToast('Document deleted.');
            });
        });
    }

    /**
     * Set up the participant add/edit modal.
     */
    function initParticipantModal() {
        $('#lw-btn-add-participant').on('click', function() {
            $('#lw-modal-title').text('Add Participant');
            $('#lw-participant-id').val('0');
            $('#lw-participant-form')[0].reset();
            $('#lw-participant-modal').show();
        });

        $('#lw-modal-cancel, .lw-modal-overlay').on('click', function() {
            $(this).closest('.lw-modal').hide();
        });

        $('#lw-participant-form').on('submit', function(e) {
            e.preventDefault();
            var formdata = $(this).serializeArray();
            var data = {action: 'save_participant', workshopid: workshopId};
            $.each(formdata, function(i, f) { data[f.name] = f.value; });
            ajaxRequest(data, function(resp) {
                $('#lw-participant-modal').hide();
                showToast(resp.message || 'Participant saved. Reloading...');
                setTimeout(function() { location.reload(); }, 800);
            });
        });
    }

    /**
     * Group member counts keyed by group id, populated when groups load.
     */
    var groupCounts = {};

    /**
     * Set up the "Enrol from Group" modal.
     */
    function initGroupEnrolModal() {
        var $modal  = $('#lw-group-modal');
        if (!$modal.length) return; // No linked course — button not rendered

        var $btn    = $('#lw-btn-enrol-group');
        var $select = $('#lw-group-select');
        var $count  = $('#lw-group-member-count');
        var $form   = $('#lw-group-form');
        var $loading   = $('#lw-group-loading');
        var $noGroups  = $('#lw-group-no-groups');
        var $noCourse  = $('#lw-group-no-course');
        var $submitBtn = $('#lw-group-submit');

        // Open modal and load groups
        $btn.on('click', function() {
            // Reset state
            $form.hide();
            $loading.show();
            $noGroups.hide();
            $noCourse.hide();
            $modal.show();

            ajaxRequest({action: 'get_groups', workshopid: workshopId}, function(resp) {
                $loading.hide();

                if (!resp.haslinkedcourse) {
                    $noCourse.show();
                    return;
                }

                if (!resp.groups || resp.groups.length === 0) {
                    $noGroups.show();
                    return;
                }

                // Populate select
                $select.empty();
                groupCounts = {};
                $.each(resp.groups, function(i, g) {
                    groupCounts[g.id] = g.count;
                    $select.append(
                        $('<option></option>').val(g.id).text(g.name + ' (' + g.count + ' member' + (g.count !== 1 ? 's' : '') + ')')
                    );
                });

                // Show member count hint for initial selection
                updateMemberCount();
                $form.show();

            }, function(err) {
                $loading.hide();
                showToast('Failed to load groups: ' + err, 'error');
                $modal.hide();
            });
        });

        // Update member count hint when selection changes
        $select.on('change', function() {
            updateMemberCount();
        });

        function updateMemberCount() {
            var gid   = $select.val();
            var count = groupCounts[gid] !== undefined ? groupCounts[gid] : 0;
            $count.text(count + ' member' + (count !== 1 ? 's' : '') + ' will be processed.');
        }

        // Close modal
        $('.lw-group-modal-overlay, .lw-group-cancel').on('click', function() {
            $modal.hide();
        });

        // Submit — enrol all group members
        $form.on('submit', function(e) {
            e.preventDefault();
            var groupid = $select.val();
            if (!groupid) { showToast('Please select a group.', 'error'); return; }

            var bookingstatus = $form.find('select[name="bookingstatus"]').val();
            var enrolcourse   = $form.find('#lw-enrol-course-chk').is(':checked') ? 1 : 0;

            $submitBtn.prop('disabled', true).text('Enrolling...');

            ajaxRequest({
                action:        'enrol_group',
                workshopid:    workshopId,
                groupid:       groupid,
                bookingstatus: bookingstatus,
                enrolcourse:   enrolcourse
            }, function(resp) {
                $modal.hide();
                $submitBtn.prop('disabled', false).text('Enrol Group');
                showToast(resp.message || 'Group enrolled.', 'info');
                setTimeout(function() { location.reload(); }, 900);
            }, function(err) {
                $submitBtn.prop('disabled', false).text('Enrol Group');
                showToast('Error: ' + err, 'error');
            });
        });
    }

    /**
     * Set up the bulk "Attendance" modal.
     */
    function initAttendanceModal() {
        var $modal = $('#lw-attendance-modal');
        if (!$modal.length) return; // No participants — button/modal not rendered

        var $form   = $('#lw-attendance-form');
        var $submit = $('#lw-att-submit');
        var $search = $('#lw-att-search');
        var $master = $('#lw-att-master');
        var initial = {};

        // Remember the state the page loaded with so Cancel restores it.
        $form.find('.lw-att-chk').each(function() {
            initial[this.value] = this.checked;
        });

        function visibleChecks() {
            return $form.find('.lw-att-row:visible .lw-att-chk');
        }

        function updateCount() {
            var all      = $form.find('.lw-att-chk');
            var selected = all.filter(':checked').length;
            $('#lw-att-count').text(selected + ' of ' + all.length + ' selected');
            var vis    = visibleChecks();
            var visSel = vis.filter(':checked').length;
            $master.prop('checked', vis.length > 0 && visSel === vis.length);
            $master.prop('indeterminate', visSel > 0 && visSel < vis.length);
            $form.find('.lw-att-row').each(function() {
                $(this).toggleClass('lw-att-selected', $(this).find('.lw-att-chk').is(':checked'));
            });
        }

        $('#lw-btn-attendance').on('click', function() {
            $form.find('.lw-att-chk').each(function() {
                this.checked = !!initial[this.value];
            });
            $search.val('');
            $form.find('.lw-att-row').show();
            $('#lw-att-unticked').val('unchanged');
            $('#lw-att-sendemail').prop('checked', true);
            updateCount();
            $modal.show();
            $search.trigger('focus');
        });

        $modal.on('click', '.lw-attendance-cancel', function() {
            $modal.hide();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.hide();
            }
        });

        // Select / unselect act on the rows currently shown by the filter.
        $('#lw-att-selectall').on('click', function() {
            visibleChecks().prop('checked', true);
            updateCount();
        });
        $('#lw-att-selectnone').on('click', function() {
            visibleChecks().prop('checked', false);
            updateCount();
        });
        $master.on('change', function() {
            visibleChecks().prop('checked', this.checked);
            updateCount();
        });
        $form.on('change', '.lw-att-chk', updateCount);

        // Click anywhere on a row to toggle it.
        $form.on('click', '.lw-att-row td', function(e) {
            if ($(e.target).is('input, label')) return;
            var chk = $(this).closest('tr').find('.lw-att-chk')[0];
            chk.checked = !chk.checked;
            updateCount();
        });

        $search.on('input', function() {
            var q = $.trim($(this).val()).toLowerCase();
            $form.find('.lw-att-row').each(function() {
                var hay = String($(this).data('search') || '');
                $(this).toggle(q === '' || hay.indexOf(q) !== -1);
            });
            updateCount();
        });

        // Stop Enter in the filter box submitting the form.
        $search.on('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });

        $form.on('submit', function(e) {
            e.preventDefault();
            var ids = [];
            $form.find('.lw-att-chk:checked').each(function() { ids.push(this.value); });
            var unticked = $('#lw-att-unticked').val();

            if (unticked === 'noshow') {
                var noshow = $form.find('.lw-att-chk').length - ids.length;
                if (noshow > 0 && !confirm(noshow + ' unticked participant' + (noshow !== 1 ? 's' : '') +
                        ' will be marked No Show. Continue?')) {
                    return;
                }
            }

            $submit.prop('disabled', true).text('Saving...');
            ajaxRequest({
                action:     'bulk_attendance',
                workshopid: workshopId,
                attended:   ids, // jQuery serialises as attended[]=..
                unticked:   unticked,
                sendemail:  ($('#lw-att-sendemail').length && !$('#lw-att-sendemail').is(':checked')) ? 0 : 1
            }, function(resp) {
                $modal.hide();
                showToast(resp.message || 'Attendance saved.', 'info');
                setTimeout(function() { location.reload(); }, 2500);
            }, function(err) {
                $submit.prop('disabled', false).text('Save Attendance');
                showToast('Error: ' + err, 'error');
            });
        });
    }

    /**
     * Recalculate and update the progress bar.
     */
    function updateProgressBar() {
        var total     = $('.lw-task-item').length;
        var completed = $('.lw-task-item.lw-task-complete').length;
        if (total === 0) return;
        var pct = Math.round((completed / total) * 100);
        $('.lw-progress-fill').css('width', pct + '%');
        $('.lw-progress-label').text(completed + ' / ' + total + ' tasks complete (' + pct + '%)');
    }

    return {
        init: function() {
            initManagePage();
        },
        initView: function(wid, sk) {
            initViewPage(wid, sk);
        }
    };
});
