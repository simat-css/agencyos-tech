document.addEventListener('DOMContentLoaded', function () {


    const config = document.getElementById('company-config');


    if (!config) {
        return;
    }


    const bulkUrl = config.dataset.bulkUrl;



    /*
    |--------------------------------------------------------------------------
    | Select All
    |--------------------------------------------------------------------------
    */


    document
    .getElementById('select-all-companies')
    ?.addEventListener('change', function () {


        document
        .querySelectorAll('.company-checkbox')
        .forEach(cb => {

            cb.checked = this.checked;

        });


    });



    /*
    |--------------------------------------------------------------------------
    | Bulk Action
    |--------------------------------------------------------------------------
    */


    function bulkAction(action) {


        let ids = [];


        document
        .querySelectorAll('.company-checkbox:checked')
        .forEach(cb => {

            ids.push(cb.value);

        });



        if(ids.length === 0){


            Swal.fire({

                icon:'warning',

                title:'Warning',

                text:'Please select companies.'

            });


            return;

        }



        fetch(bulkUrl, {


            method:'POST',


            headers:{


                'Content-Type':'application/json',

                'Accept':'application/json',


                'X-CSRF-TOKEN':
                document
                .querySelector('meta[name="csrf-token"]')
                .content


            },


            body:JSON.stringify({

                action:action,

                ids:ids

            })


        })


        .then(async response=>{


            const data = await response.json();


            if(!response.ok){

                throw new Error(
                    data.message ?? 'Something went wrong.'
                );

            }


            return data;


        })


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



        })


        .catch(error=>{


            Swal.fire({

                icon:'warning',

                title:'Notice',

                text:error.message

            });


        });



    }




    /*
    |--------------------------------------------------------------------------
    | Bulk Delete Confirmation
    |--------------------------------------------------------------------------
    */


    document
    .getElementById('bulk-delete')
    ?.addEventListener('click',function(){


        let ids=[];

        let totalDepartments=0;



        document
        .querySelectorAll('.company-checkbox:checked')
        .forEach(cb=>{


            ids.push(cb.value);


            totalDepartments += parseInt(
                cb.dataset.departments || 0
            );


        });



        if(ids.length===0){


            Swal.fire({

                icon:'warning',

                title:'Warning',

                text:'Please select companies.'

            });


            return;

        }



        let message =
        `You are about to delete ${ids.length} selected compan${ids.length > 1 ? 'ies':'y'}.`;



        if(totalDepartments > 0){


            message +=
            `\n\nThese companies contain ${totalDepartments} department(s).`;


            message +=
            `\nDeleting the companies will also delete all associated departments.`;

        }



        message +=
        '\n\nThis action cannot be undone.';



        Swal.fire({


            icon:'warning',

            title:'Delete Selected Companies?',


            text:message,


            showCancelButton:true,


            confirmButtonText:'Yes, Delete',


            cancelButtonText:'Cancel',


            confirmButtonColor:'#d33'



        })


        .then(result=>{


            if(result.isConfirmed){

                bulkAction('delete');

            }


        });



    });





    /*
    |--------------------------------------------------------------------------
    | Bulk Activate / Deactivate
    |--------------------------------------------------------------------------
    */


    document
    .getElementById('bulk-activate')
    ?.addEventListener('click',()=>{


        bulkAction('activate');


    });



    document
    .getElementById('bulk-deactivate')
    ?.addEventListener('click',()=>{


        bulkAction('deactivate');


    });





    /*
    |--------------------------------------------------------------------------
    | Company Status Warning
    |--------------------------------------------------------------------------
    */


    document
    .querySelectorAll('.company-status-form')
    .forEach(form=>{


        form.addEventListener('submit',function(e){



            const currentStatus =
            this.dataset.status;



            if(currentStatus !== 'active'){

                return;

            }



            e.preventDefault();



            const companyName =
            this.dataset.company;



            const activeDepartments =
            parseInt(
                this.dataset.departments || 0
            );



            let message =
            `Company "${companyName}" will be deactivated.`;



            if(activeDepartments > 0){


                message +=
                `\n\n${activeDepartments} active department(s) will also be automatically deactivated.`;

            }



            message +=
            '\n\nDo you want to continue?';



            Swal.fire({


                icon:'warning',


                title:'Deactivate Company?',


                text:message,


                showCancelButton:true,


                confirmButtonText:'Yes, Deactivate',


                cancelButtonText:'Cancel'


            })


            .then(result=>{


                if(result.isConfirmed){

                    form.submit();

                }


            });



        });


    });



});