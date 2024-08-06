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
<div style="width: 100% ; display: flex; clear:both; position:relative;border-color">
    <div style="background-color: #058c61; width: 30%;position:absolute; left:0pt;top:0pt;bottom:0pt;padding-left:1%;min-height: 100%">

        <div style="align-items:center;justify-content:center;margin-left:40px">
            <img
                className='ms-5'
                style="
                    height: 150px;
                    width: 150px
                "
                src='./images/central.png'
            />
        </div>

        <div
            style="margin-top:30px;display:flex;align-items:center;justify-content:center;text-align:center"
        >
            <span style="color: white; font-weight: bold">
                Barangay Council
            </span>
        </div>

        <div
            style=" margin-top: 30px;justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold; font-size: 10px" 
            >
                HON. RODOLFO E. TANGPUZ II </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                BARANGAY CHAIRMAN
            </span>
        </div>

        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                HON. MARIA CECILIA T. BALMORI </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                KAGAWAD FOR CULTURAL & SPORT
            </span>
        </div>

        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                HON. VENADIK M. CASTRO </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                KAGAWAD CHAIRMAN FOR PEACE & ORDER
            </span>
        </div>

        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                HON. LEAH M. PEREZ </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                KAGAWAD
                CHAIRMAN FOR APPROPRIATION,
                EDUCATION & INFORMATION
                DISSEMINATION
            </span>
        </div>

        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                HON. OLIVER G. OSANO </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                KAGAWAD CHAIRMAN FOR TRANSPORTATION
            </span>
        </div>

        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                HON. PIULY B. DULANG </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                CHAIRMAN FOR HEALTH & ENVIRONMENT SANITATION
            </span>
        </div>

        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                HON. ALYNN REIGN A. RAFIÑAN </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                SK CHAIRPERSON
            </span>
        </div>




        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                OLGA H. CALAYO </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                BARANGAY SECRETARY
            </span>
        </div>

        <div
            style=" margin-top: 30px; display: flex; flex-direction: column; justify-content: center; marginLeft: 15px"
        >
            <span
                style=" color:white;font-weight: bold;font-size: 10px" 
            >
                LILIA T. AMADOR </br>
            </span>
            <span
                style=" color: white;font-size:10px"
            >
                BARANGAY TREASURER
            </span>
        </div>






    </div>

    <div 
        style="backgroundClip:white;width:70%;margin-left:31%;align-items:center;text-align:center"
    >
        <div
            style="
                display:flex; align-items:center; justify-content:space-between;margin-top:50px"
        >
            <div style="clear:both; position:relative">

            <div style="position:absolute; left:0pt; margin-top: 30px;margin-left:50px">
                <div
                    style="display:flex; flex-direction:column; align-items:center; justify-content:center"
                >
                    <h4  style="font-weight:normal">
                        REPUBLIC OF THE PHILIPPINES
                    </h4>
                    <h4  style="font-weight:normal">
                        CITY OF TAGUIG
                    </h4>
                </div>

                <div 
                     style="flex-direction:column; align-items:center; justify-content:center"
                >
                    <h4  style="font-weight:bold">
                        BARANGAY CENTRAL BICUTAN
                    </h4>
                </div>
            </div>
            <div style="margin-left:300px">
            <img
                style="
                    height: 120px;
                    width: 120px;"
                src='./images/taguig.png'
            />
            </div>
            </div>
        </div>


        <div 
            style="display:'flex', align-items:'center', justify-content:center; margin-top:50px"
        >
            <h4 className='fw-normal'>
                OFFICE OF THE BARANGAY CHAIRMAN
            </h4>
        </div>

        <div 
            style="margin-top:50px;display:flex; flex-direction:column; align-items:center; justify-content:center"
        >
            <h4 id='document-title' >
                {!! $title !!}
            </h4>
        </div>
        <div id='document-body' style=" height: 520px ;position: relative;left:0pt;width:100%;text-align:left;padding-left: 15px;padding-right:15px">
            {!! $html_code !!}
        </div>
        <div className='flex-column d-flex align-items-end pe-5'
            style="flex-direction:column; display:flex; align-items:center; padding-right:50px;clear:both; position:relative"
        >
            <span    style="font-weight:bold;position:absolute; right:20pt">
                HON. RODOLFO E. TANGPUZ II
            </span>
            <span style="font-style: italic;position:absolute; right:20pt;margin-top:15pt">
                Barangay Chairman
            </span>
        </div>


    </div>

</div>