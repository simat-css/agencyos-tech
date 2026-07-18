document.addEventListener('DOMContentLoaded', function () {


    const selectAll = document.getElementById(
        'select-all-notifications'
    );


    selectAll?.addEventListener(
        'change',
        function () {

            document
                .querySelectorAll('.notification-checkbox')
                .forEach(cb => {

                    cb.checked = this.checked;

                });

        }
    );



    const bulkDelete = document.getElementById(
        'bulk-delete-notifications'
    );


    bulkDelete?.addEventListener(
        'click',
        function () {


            let ids = [];


            document
                .querySelectorAll(
                    '.notification-checkbox:checked'
                )
                .forEach(cb => {

                    ids.push(cb.value);

                });



            if(ids.length === 0){

                Swal.fire({

                    icon:'warning',

                    title:'Warning',

                    text:'Please select notifications.'

                });

                return;

            }



            Swal.fire({

                icon:'warning',

                title:'Delete Selected Notifications?',

                text:
                `You are about to delete ${ids.length} notifications.`,

                showCancelButton:true,

                confirmButtonText:'Yes, Delete',

                cancelButtonText:'Cancel'


            }).then((result)=>{


                if(result.isConfirmed){


                    fetch('/notifications/bulk-delete',
                    {

                        method:'POST',

                        headers:{

                            'Content-Type':
                            'application/json',

                            'Accept':
                            'application/json',

                            'X-CSRF-TOKEN':
                            document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            .content

                        },


                        body:JSON.stringify({

                            ids:ids

                        })


                    })
                    .then(response=>response.json())

                    .then(data=>{


                        Swal.fire({

                            icon:'success',

                            title:data.message,

                            timer:1500,

                            showConfirmButton:false

                        });


                        setTimeout(()=>{

                            location.reload();

                        },1500);


                    });


                }


            });


        });
//Delete alert
document.querySelectorAll('.delete-notification-form')
    .forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({

                icon: 'warning',

                title: 'Delete Notification?',

                text: 'This notification will be deleted.',

                showCancelButton: true,

                confirmButtonText: 'Yes, Delete',

                cancelButtonText: 'Cancel',

                confirmButtonColor: '#dc3545'

            }).then((result) => {

                if (result.isConfirmed) {

                    form.submit();

                }

            });

        });

    });

});