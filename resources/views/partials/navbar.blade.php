<nav class="navbar navbar-expand navbar-dark top-navbar">

    <div class="container-fluid">


        {{-- Sidebar Toggle --}}
        <button class="btn text-white border-0">
            <i class="fas fa-bars fs-5"></i>
        </button>



        {{-- Brand --}}
        <a class="navbar-brand ms-3 fw-bold">

            <i class="fas fa-layer-group me-2"></i>

            AgencyOS

        </a>




        {{-- Right Section --}}
        <div class="ms-auto d-flex align-items-center gap-4">



            {{-- Global Search --}}
            <div class="search-wrapper">


                <div class="input-group">


                    <span class="input-group-text bg-transparent border-0 text-white">

                        <i class="fas fa-search"></i>

                    </span>



                    <input
                        type="text"
                        id="globalSearch"
                        class="form-control search-box"
                        placeholder="Search users, companies, roles..."
                    >


                </div>



                {{-- Search Result --}}
                <div id="searchResult"
                     class="search-result shadow">

                </div>


            </div>






            {{-- Notifications --}}
            <a href="#"
               class="nav-icon position-relative text-white">


                <i class="fas fa-bell fs-5"></i>


                <span class="badge-count">
                    3
                </span>


            </a>







            {{-- User Dropdown --}}
            <div class="dropdown">


                <a href="#"
                   class="d-flex align-items-center text-white text-decoration-none"
                   data-bs-toggle="dropdown">



                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                        width="42"
                        height="42"
                        class="rounded-circle border"
                    >



                    <div class="ms-2">


                        <div class="fw-semibold">

                            {{ auth()->user()->name }}

                        </div>



                        <small class="text-light">


                            {{ auth()->user()->getRoleNames()->first() ?? 'User' }}


                        </small>


                    </div>



                    <i class="fas fa-chevron-down ms-3"></i>



                </a>





                <ul class="dropdown-menu dropdown-menu-end shadow">



                    <li>

                        <a href="#"
                           class="dropdown-item">


                            <i class="fas fa-user me-2"></i>

                            Profile


                        </a>


                    </li>





                    <li>

                        <a href="#"
                           class="dropdown-item">


                            <i class="fas fa-history me-2"></i>

                            Login History


                        </a>


                    </li>





                    <li>

                        <hr class="dropdown-divider">

                    </li>





                    <li>


                        <form action="{{ route('logout') }}"
                              method="POST">


                            @csrf



                            <button type="submit"
                                    class="dropdown-item text-danger">


                                <i class="fas fa-sign-out-alt me-2"></i>

                                Logout


                            </button>


                        </form>


                    </li>



                </ul>



            </div>




        </div>



    </div>


</nav>



@push('scripts')

<script>

document.addEventListener("DOMContentLoaded", function(){


    const searchInput = document.getElementById('globalSearch');
    const resultBox = document.getElementById('searchResult');


    if(!searchInput || !resultBox){
        return;
    }



    searchInput.addEventListener('keyup', function(){


        let keyword = this.value.trim();



        if(keyword.length < 2){

            resultBox.innerHTML = '';

            resultBox.style.display = 'none';

            return;

        }




        fetch(`{{ route('global.search') }}?q=${keyword}`)


        .then(response => response.json())


        .then(data => {


            let html = '';



            // Users Result

            if(data.users && data.users.length > 0){


                data.users.forEach(user => {


                    html += `

                    <div class="result-item"
                         data-name="${user.name}">


                        <i class="fas fa-user me-2"></i>


                        <span>
                            ${user.name}
                        </span>


                        <small class="d-block text-muted ms-4">
                            ${user.email}
                        </small>


                    </div>


                    `;


                });


            }







            // Companies Result

            if(data.companies && data.companies.length > 0){


                data.companies.forEach(company => {


                    html += `


                    <div class="result-item"
                         data-name="${company.name}">


                        <i class="fas fa-building me-2"></i>


                        ${company.name}


                    </div>


                    `;


                });


            }







            // Roles Result

            if(data.roles && data.roles.length > 0){


                data.roles.forEach(role => {


                    html += `


                    <div class="result-item"
                         data-name="${role.name}">


                        <i class="fas fa-user-shield me-2"></i>


                        ${role.name}


                    </div>


                    `;


                });


            }







            // No Result

            if(html === ''){


                html = `


                <div class="result-item text-muted">


                    No result found


                </div>


                `;


            }



            resultBox.innerHTML = html;


            resultBox.style.display = 'block';



        })


        .catch(error => {


            console.log("Search Error:", error);


        });



    });







    // Click on suggestion

    resultBox.addEventListener('click', function(e){


        let item = e.target.closest('.result-item');



        if(item){


            let selectedValue = item.getAttribute('data-name');



            searchInput.value = selectedValue;



            resultBox.innerHTML = '';

            resultBox.style.display = 'none';



            console.log("Selected:", selectedValue);



        }


    });








    // Hide dropdown when click outside

    document.addEventListener('click', function(e){


        if(!e.target.closest('.search-wrapper')){


            resultBox.innerHTML = '';

            resultBox.style.display = 'none';


        }


    });



});

</script>

@endpush