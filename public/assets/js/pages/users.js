document.addEventListener('DOMContentLoaded', function () {

const userRoutes = document.getElementById('user-routes');

const bulkActionRoute = userRoutes.dataset.bulkAction;

const toggleBaseUrl = userRoutes.dataset.toggleUrl;

    // Status Toggle
    document.querySelectorAll('.status-toggle').forEach(function (toggle) {

        toggle.addEventListener('change', function () {

            const userId = this.dataset.id;

            fetch(`${toggleBaseUrl}/${userId}/toggle-status`, {

                method: 'PATCH',

                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .content,

                    'Accept': 'application/json'
                }

            })
            .then(async response => {

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to update status.');
                }

                return data;

            })
            .then(data => {

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                setTimeout(() => {
                    location.reload();
                }, 1500);

            })
            .catch(error => {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });

                setTimeout(() => {
                    location.reload();
                }, 1000);

            });

        });

    });

    // Select All Users
    document.getElementById('select-all-users')
    ?.addEventListener('change', function () {

        document.querySelectorAll('.user-checkbox')
        .forEach(cb => {
            cb.checked = this.checked;
        });

    });

    // Bulk Action Function
    function userBulkAction(action) {

        let ids = [];

        document.querySelectorAll('.user-checkbox:checked')
        .forEach(cb => {
            ids.push(cb.value);
        });

        if (ids.length === 0) {

            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select users.'
            });

            return;
        }

        fetch(bulkActionRoute, {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .content
            },

            body: JSON.stringify({
                action: action,
                ids: ids
            })

        })
        .then(async response => {

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message);
            }

            return data;

        })
        .then(data => {

            Swal.fire({
                icon: 'success',
                title: data.message,
                timer: 1500,
                showConfirmButton: false
            });

            setTimeout(() => {
                location.reload();
            }, 1500);

        })
        .catch(error => {

            Swal.fire({
                icon: 'warning',
                title: 'Notice',
                text: error.message
            });

        });

    }

    // Bulk Activate
    document.getElementById('bulk-user-activate')
    ?.addEventListener('click', () => userBulkAction('activate'));

    // Bulk Deactivate
    document.getElementById('bulk-user-deactivate')
    ?.addEventListener('click', () => userBulkAction('deactivate'));

    // Bulk Delete Confirmation
    document.getElementById('bulk-user-delete')
    ?.addEventListener('click', () => {

        Swal.fire({
            title: 'Delete Selected Users?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        })
        .then(result => {

            if (result.isConfirmed) {
                userBulkAction('delete');
            }

        });

    });

    // Single User Delete
    document.querySelectorAll('.delete-user-btn')
    .forEach(button => {

        button.addEventListener('click', function () {

            let userId = this.dataset.id;
            let userName = this.dataset.name;

            Swal.fire({

                title: 'Delete User?',
                html: `Are you sure you want to delete <strong>${userName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'

            }).then((result) => {

                if (result.isConfirmed) {

                    document
                        .getElementById(`delete-user-form-${userId}`)
                        .submit();

                }

            });

        });

    });

});