@extends ('layouts/app')
@section ('content')
<div class='w-100 d-flex'>
    <div class='col-4 bg-green'>

        <div class='d-flex align-items-center justify-content-center mt-4'>
        <img    
            class='ms-5'
                    style="
                        height: 150px;
                        width: 150px
                    "
                    src='/images/central.png'
                />
        </div>

        <div class='mt-5 d-flex alig-items-center justify-content-center'>
            <span class='f-white fw-bold'>
                Barangay Council
            </span>
        </div>

        <div class='mt-5 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. RODOLFO E. TANGPUZ II
            </span>
            <span class='f-white tempate-font-officials'>
                BARANGAY CHAIRMAN
            </span>
        </div>

        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. MARIA CECILIA T. BALMORI
            </span>
            <span class='f-white tempate-font-officials'>
                KAGAWAD FOR CULTURAL & SPORT
            </span>
        </div>

        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. VENADIK M. CASTRO
            </span>
            <span class='f-white tempate-font-officials'>
                KAGAWAD CHAIRMAN FOR PEACE & ORDER
            </span>
        </div>

        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. LEAH M. PEREZ
            </span>
            <span class='f-white tempate-font-officials'>
                KAGAWAD
                CHAIRMAN FOR APPROPRIATION,
                EDUCATION & INFORMATION
                DISSEMINATION
            </span>
        </div>


        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. CRISTINA O. SANARES
            </span>
            <span class='f-white tempate-font-officials'>
                KAGAWAD
                CHAIRMAN FOR ELDERLY & PDAO,
                FAMILY AFFAIRS
            </span>
        </div>


        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. OLIVER G. OSANO
            </span>
            <span class='f-white tempate-font-officials'>
                KAGAWAD CHAIRMAN FOR TRANSPORTATION
            </span>
        </div>

        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. PIULY B. DULANG
            </span>
            <span class='f-white tempate-font-officials'>
                CHAIRMAN FOR HEALTH & ENVIRONMENT SANITATION
            </span>
        </div>


        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                HON. ALYNN REIGN A. RAFIÑAN
            </span>
            <span class='f-white tempate-font-officials'>
                SK CHAIRPERSON
            </span>
        </div>

        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                OLGA H. CALAYO
            </span>
            <span class='f-white tempate-font-officials'>
                BARANGAY SECRETARY
            </span>
        </div>

        <div class='mt-4 d-flex flex-column  justify-content-center ms-3'>
            <span class='f-white fw-bold tempate-font-officials'>
                LILIA T. AMADOR
            </span>
            <span class='f-white tempate-font-officials'>
                BARANGAY TREASURER
            </span>
        </div>


    </div>

    <div class='col-8 bg-white'>
        <div class='d-flex align-items-center justify-content-center mt-5'>
            <div>
                <div class='d-flex flex-column align-items-center justify-content-center'>
                    <h4 class='fw-normal'>
                        REPUBLIC OF THE PHILIPPINES
                    </h4>
                    <h4 class='fw-normal'>
                        CITY OF TAGUIG
                    </h4>
                </div>

                <div class='d-flex flex-column align-items-center justify-content-center'>
                    <h4 class='bold'>
                        BARANGAY CENTRAL BICUTAN
                    </h4>
                </div>
            </div>

            <img    
            class='ms-5'
                    style="
                    height: 120;
                    width: 150px
                "
                    src='/images/taguig.png'
                />
        </div>


        <div class='mt-5 d-flex flex-column align-items-center justify-content-center'>
            <h4 class='fw-normal'>
                OFFICE OF THE BARANGAY CHAIRMAN
            </h4>
        </div>

        <div class='mt-4 d-flex flex-column align-items-center justify-content-center'>
            <h4 id='document-title' class=''>
                BARANGAY CLEARANCE
            </h4>
        </div>


        <div id='document-body' class='mt-4 d-flex justify-content-center' style="height:520px">
            {/* BODY */}
        </div>

        <div class='flex-column d-flex align-items-end pe-5'>
            <span class='fw-bold'>
                HON. RODOLFO E. TANGPUZ II
            </span>
            <span class='fst-italic'>
                Barangay Chairman
            </span>
        </div>


    </div>

</div>
@endsection