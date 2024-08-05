<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
        --max-width: 1100px;
        --border-radius: 12px;
        --font-mono: ui-monospace, Menlo, Monaco, "Cascadia Mono", "Segoe UI Mono",
            "Roboto Mono", "Oxygen Mono", "Ubuntu Monospace", "Source Code Pro",
            "Fira Mono", "Droid Sans Mono", "Courier New", monospace;

        --foreground-rgb: 0, 0, 0;
        --background-start-rgb: 214, 219, 220;
        --background-end-rgb: 255, 255, 255;

        --primary-glow: conic-gradient(
            from 180deg at 50% 50%,
            #16abff33 0deg,
            #0885ff33 55deg,
            #54d6ff33 120deg,
            #0071ff33 160deg,
            transparent 360deg
        );
        --secondary-glow: radial-gradient(
            rgba(255, 255, 255, 1),
            rgba(255, 255, 255, 0)
        );

        --tile-start-rgb: 239, 245, 249;
        --tile-end-rgb: 228, 232, 233;
        --tile-border: conic-gradient(
            #00000080,
            #00000040,
            #00000030,
            #00000020,
            #00000010,
            #00000010,
            #00000080
        );

        --callout-rgb: 238, 240, 241;
        --callout-border-rgb: 172, 175, 176;
        --card-rgb: 180, 185, 188;
        --card-border-rgb: 131, 134, 135;
        }
        @media (prefers-color-scheme: dark) {
        :root {
            --foreground-rgb: 255, 255, 255;
            --background-start-rgb: 0, 0, 0;
            --background-end-rgb: 0, 0, 0;

            --primary-glow: radial-gradient(rgba(1, 65, 255, 0.4), rgba(1, 65, 255, 0));
            --secondary-glow: linear-gradient(
            to bottom right,
            rgba(1, 65, 255, 0),
            rgba(1, 65, 255, 0),
            rgba(1, 65, 255, 0.3)
            );

            --tile-start-rgb: 2, 13, 46;
            --tile-end-rgb: 2, 5, 19;
            --tile-border: conic-gradient(
            #ffffff80,
            #ffffff40,
            #ffffff30,
            #ffffff20,
            #ffffff10,
            #ffffff10,
            #ffffff80
            );

            --callout-rgb: 20, 20, 20;
            --callout-border-rgb: 108, 108, 108;
            --card-rgb: 100, 100, 100;
            --card-border-rgb: 200, 200, 200;
        }
        }

        * {
        box-sizing: border-box;
        padding: 0;
        margin: 0;
        }

        html,
        body {
        max-width: 100vw;
        overflow-x: hidden;
        }

        body {
        color: rgb(var(--foreground-rgb));
        background: linear-gradient(
            to bottom,
            transparent,
            rgb(var(--background-end-rgb))
            )
            rgb(var(--background-start-rgb));
        }

        a {
        color: inherit;
        text-decoration: none;
        }

        @media (prefers-color-scheme: dark) {
        html {
            color-scheme: dark;
        }
        }


        .bg-white{
        background-color: #FFFF;
        flex: 1 0 auto;
        height: 100vh;
        }

        .f-white{
        color: #FFFF;
        }

        .active-nav{
        background-color: #FEDB34;
        }


        .my-button{
        background-color: yellow;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        }

        .row-item-container:nth-child(even){
        background-color: #057350;
        }


        .f-yellow{
        color: #FEDB34;
        }
        .bg-yellow{
        background-color: #FEDB34;
        }

        .f-green{
        color: #058c61;
        }

        .bg-green{
        background-color: #058c61;
        }

        /* Colours */


        /* bg images */

        .side-bg{
        background-image: url('../assets//Sidebar_Base.png');
        }

        .bg-1{
        background-image: url('../assets//Sidebar_Base.png');
            background-size: cover;
            background-position: center;
        /* background-repeat:inherit; */
        }

        .bg-2{
        background-image: url('../assets//CoverPicture.png');
        background-size: cover;
        background-position: center;
        }

        .logo-bg{
        background-image: url('../assets//TitleforSideBar.png');
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        }

        .table-mh{
        max-height: 720px;
        overflow: scroll;
        }

        .table-mh::-webkit-scrollbar{
        display: none;
        }

        .pointer{
        cursor: pointer;
        }

        .logo-size{
        height: 100px;
        width: 100px;
        }

        .box {
        
        background-color: #f4f4f4;
        box-shadow: 5px 5px 10px rgba(0.5, 0, 0, 0.5);
        }

        .search-item{
        padding: 10px;
        cursor: pointer;
        }

        .icon{
        font-size: 30px;
        }

        .nav-item{
        font-size: 20px;
        }



        .controls {
        display: flex;
        border: 1px solid #ccc;
        border-top: 0;
        padding: 10px;
        }

        .controls-right {
        margin-left: auto;
        }

        .state {
        margin: 10px 0;
        font-family: monospace;
        }

        .state-title {
        color: #999;
        text-transform: uppercase;
        }

        .tempate-font-officials{
        font-size: 10px;
        }
    </style>
</head>
<div class='w-100 d-flex' style = "clear:both; position:relative;min-height: 100%;">
        <div class='col-4 bg-green' style="position:absolute; left:0pt; width:180pt;">

            <div class='d-flex align-items-center justify-content-center mt-4'>
            <img    
                class='ms-5'
                        style="
                        height: 150px;
                        width: 150px
                        "
                        src='./images/central.png'
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

        <div class='col-8 bg-white' style="margin-left:200pt">
            <div class='d-flex align-items-center justify-content-center mt-5' style="clear:both; position:relative">
                <div style="position:absolute; left:0pt; width:200pt;top:12pt">
                    <div class='d-flex flex-column align-items-center justify-content-center' style="justify-content:center:align-content:center">
                        <h4 class='fw-normal' style="font-weight: 400;text-align:center">
                            REPUBLIC OF THE PHILIPPINES
                        </h4>
                        <h4 class='fw-normal' style="text-align:center">
                            CITY OF TAGUIG
                        </h4>
                    </div>

                    <div class='d-flex flex-column align-items-center justify-content-center'>
                        <h4 class='bold' style="text-align:center">
                            BARANGAY CENTRAL BICUTAN
                        </h4>
                    </div>
                </div>
                <div style="margin-left:200pt">
                    <img    
                        class='ms-5'
                        style="
                            height: 120px;
                            width: 120px
                            "
                        src='./images/taguig.png'
                    />
                </div>
            </div>


            <div class='mt-5 d-flex flex-column align-items-center justify-content-center' style="margin-right:40pt">
                <h4 class='fw-normal' style="text-align:center">
                    OFFICE OF THE BARANGAY CHAIRMAN
                </h4>
            </div>

            <div class='mt-4 d-flex flex-column align-items-center justify-content-center' style="margin-right:40pt">
                <h4 id='document-title' class='' style="text-align:center">
                    BARANGAY CLEARANCE
                </h4>
            </div>


            <div id='document-body' class='' style="height:520px">
                {!!$html_code!!}
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