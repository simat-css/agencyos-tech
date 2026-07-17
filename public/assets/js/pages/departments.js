document.addEventListener('DOMContentLoaded', function () {

const routes = document.getElementById('department-routes');

const bulkActionRoute = routes.dataset.bulkAction;

const toggleBaseUrl = routes.dataset.toggleUrl;

    // Toggle Status
    document.querySelectorAll('.department-status').forEach(function (toggle) {

    toggle.addEventListener('change', function () {

        let checkbox = this;
        let departmentId = this.dataset.id;

        let previousStatus = !checkbox.checked;


       fetch(`${toggleBaseUrl}/${departmentId}/toggle-status`, {

            method:'PATCH',

            headers:{
                'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content'),

                'Accept':'application/json'
            }

        })

        .then(async response => {

            let data = await response.json();


            if(!response.ok){

                checkbox.checked = previousStatus;

                throw new Error(data.message);

            }


            return data;

        })


        .then(data=>{


            Swal.fire({

                toast:true,
                position:'top-end',
                icon:'success',
                title:data.message,
                showConfirmButton:false,
                timer:1800

            });


        })


        .catch(error=>{


            Swal.fire({

                toast:true,
                position:'top-end',
                icon:'error',
                title:error.message,
                showConfirmButton:false,
                timer:2500

            });


        });


    });

});



    // Select All Checkbox

   // Company Wise Select All

document.querySelectorAll('.company-select-all')
.forEach(function(selectAll){

    selectAll.addEventListener('change',function(){

        let companyId = this.dataset.company;


        document
        .querySelectorAll(
            '.department-checkbox[data-company="'+companyId+'"]'
        )
        .forEach(function(cb){

            cb.checked = selectAll.checked;

        });


    });

});



    // Bulk Action

function bulkAction(action) {

    let selectedIds = [];

    document.querySelectorAll('.department-checkbox:checked')
        .forEach(cb => {
            selectedIds.push(cb.value);
        });

    if (selectedIds.length === 0) {

        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please select departments.'
        });

        return;
    }

    fetch(bulkActionRoute, {

        method: "POST",

        headers: {

            "Content-Type": "application/json",

            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .content,

            "Accept": "application/json"
        },

        body: JSON.stringify({

            action: action,
            ids: selectedIds

        })

    })

    .then(async response => {

        let data = {};

        try {
            data = await response.json();
        } catch (e) {
            data.message = "Something went wrong.";
        }

        if (!response.ok) {
            throw new Error(data.message);
        }

        return data;

    })

    .then(data => {

        Swal.fire({

            icon: 'success',
            title: data.message,
            showConfirmButton: false,
            timer: 1500

        });

        setTimeout(() => {

            window.location.reload();

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

    document.getElementById('bulk-delete')
    ?.addEventListener('click',()=>bulkAction('delete'));

    document.getElementById('bulk-activate')
    ?.addEventListener('click',()=>bulkAction('activate'));

    document.getElementById('bulk-deactivate')
    ?.addEventListener('click',()=>bulkAction('deactivate'));
document.querySelectorAll('.delete-department-btn')
.forEach(function(button){

    button.addEventListener('click', function(){

        let form = this.closest('form');

        Swal.fire({
            title: 'Delete Department?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        })
        .then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            fetch(form.action, {

                method: 'POST',

                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .content,

                    'Accept': 'application/json'
                },

                body: new FormData(form)

            })

            .then(async response => {

                let data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message);
                }

                return data;
            })

            .then(data => {

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message
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

        });

    });

});

});