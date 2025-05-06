

<!-- Bootstrap 4.3-->
<link rel="stylesheet" href="http://doxe.originlabsoft.com/assets/admin/css/bootstrap.min.css">

<!-- Theme style -->
<style>
    .content-wrapper {
        min-height: 100%;
        background-color: #f5f5f5;
        z-index: 800;
    }

    .box-img-top {
        width: 100%;
        border-top-left-radius: calc(.25rem - 1px);
        border-top-right-radius: calc(.25rem - 1px);
    }

    .box-group .box {
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .box-deck {
        display: -ms-flexbox;
        display: flex;
        -ms-flex-direction: column;
        flex-direction: column;
    }

    @media (min-width: 576px) {
        .box-group .box:first-child .box-img-top {
            border-top-right-radius: 0;
        }

        .box-group .box:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .box-group .box {
            -ms-flex: 1 0 0%;
            flex: 1 0 0%;
        }

        .box-group {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-flow: row wrap;
            flex-flow: row wrap;
        }

        .box-deck {
            -ms-flex-flow: row wrap;
            flex-flow: row wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .box-deck .box {
            display: -ms-flexbox;
            display: flex;
            -ms-flex: 1 0 0%;
            flex: 1 0 0%;
            -ms-flex-direction: column;
            flex-direction: column;
            margin-right: 15px;
            margin-bottom: 0;
            margin-left: 15px;
        }
    }

    .box-deck .box {
        margin-bottom: 20px;
    }

    .box-columns .box {
        margin-bottom: 20px;
    }

    @media (min-width: 576px) {
        .box-columns {
            -webkit-column-count: 3;
            column-count: 3;
            -webkit-column-gap: 1.25rem;
            column-gap: 1.25rem;
        }

        .box-columns .box {
            display: inline-block;
            width: 100%;
        }
    }

    .box-body {
        padding: 20px 35px;
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
    }

    .box-body.minh-200 {
        min-height: 225px;
    }

    .no-header .box-body {
        border-top-right-radius: 3px;
        border-top-left-radius: 3px;
    }

    .box-body>.table {
        margin-bottom: 0;
    }

    .box-body .fc {
        margin-top: 5px;
    }

    .box-body .full-width-chart {
        margin: -10px;
    }

    .box-body.no-padding .full-width-chart {
        margin: -9px;
    }

    .box-body .box-pane {
        border-radius: 0 0 0 3px;
    }

    .box-body .box-pane-right {
        border-radius: 0 0 3px;
    }

    .prescription_headers {
        min-height: 160px;
        margin: 40px 0 0px !important;
    }

    img.chamber-img {
        max-width: 120px;
        margin-bottom: 5px;
    }

    .wrapper,
    .content-wrapper,
    body {
        overflow-x: hidden;
        overflow-y: auto;
        background-color: #f4f6f9 !important;
    }

    .box {
        position: relative;
        border-top: 0;
        margin-bottom: 20px;
        width: 100%;
        background: #fff;
        border-radius: 0;
        padding: 0px;
        -webkit-transition: .5s;
        transition: .5s;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-direction: column;
        flex-direction: column;
        border: 1px solid #f2f2f2;
        border-radius: 8px;
        -webkit-box-shadow: 0 6px 24px #f1f1f1;
        box-shadow: 0 6px 24px #f1f1f1;
    }

    .top_status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #999;
        padding: 0 10px;
        margin: 0;
        margin-bottom: 20px;
        border-left: 0;
        border-right: 0;
    }

    .btn-lgr {
        padding: 12px 30px !important;
    }

    .left_top {
        padding-left: 52px;
    }

    .top_status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #999;
        padding: 0 10px;
        margin: 0;
        margin-bottom: 20px;
        border-left: 0;
        border-right: 0;
    }

    .right_top ul {
        display: flex;
        align-items: center;
        flex-direction: row;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .right_top ul li {
        padding: 5px 25px;
        font-weight: 600;
    }

    .right_top ul li i {
        font-size: 15px;
        font-weight: bold;
        margin-right: 5px;
    }

    .left_top i {
        font-size: 14px;
        font-weight: 600;
    }

    .rx_header h1 {
        padding: 0;
        margin: 0;
    }

    .left_p_header h4 {
        padding: 0;
        margin: 0;
        font-size: 16px;
    }

    .left_prescription ol {
        padding-left: 12px;
        margin: 0;
        line-height: 16px;
        font-size: 13px;
        font-family: 'SolaimanLipi', Arial, sans-serif !important;
    }

    .visit_left {
        width: 100%;
    }

    .visit_right {
        width: 100%;
    }

    .footer_pescript {
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: right;
        padding-right: 50px;
    }

    .dropdown.custom {
        margin-top: 15px;
        margin-right: 20px;
    }

    .group {
        margin-bottom: 15px;
    }

    .left_p_details ol p {
        padding: 0;
        margin: 0;
        font-size: 13px;
        font-family: 'SolaimanLipi', Arial, sans-serif !important;
        margin-bottom: 5px;
        font-weight: 500;
        color: #000;
    }

    p.last_visit {
        position: absolute;
        /* bottom: 0; */
        left: 10px;
        margin-top: 30px;
    }

    .right_prescription {
        position: relative;
        height: 100%;
        min-height: 400px;
    }

    .add_increase_button {
        margin-top: -5px;
    }

    .remove_field {
        margin-top: -5px;
    }

    .m-auto {
        margin: 0 auto !important;
    }

    .then {
        margin-left: -42px;
        margin-right: 5px;
    }

    .second_value p span {
        font-weight: 500;

    }

    .last_visit_pn {
        font-weight: 600;
        font-size: 15px;
        font-family: 'SolaimanLipi', Arial, sans-serif !important;
    }

    .right_pres_side {
        padding: 11px;
        width: 60%;
    }

    p.note_text {
        line-height: 15px;
        font-weight: 400;
        font-size: 14px;
        padding-right: 20px;
        margin-top: 4px;
    }

    .left_p_header.left {
        padding: 0 !important;
    }

    .d_flex_input {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .d_flex_input input {
        width: 38px;
        height: 34px;
        margin: 0 2px;
    }

    .d_flex_input i {
        font-weight: 100;
        font-size: 10px;
    }


    .prescription_body {
        display: flex;
        justify-content: flex-start;
        flex-direction: row;
        width: 100%;
        min-height: 700px;
        max-height: 700px;
        overflow: hidden;
    }

    .single_left {
        margin-bottom: 10px;
    }

    .prescription_headers {
        min-height: 160px;
        margin: 40px 0 70px;
    }

    h3 {
        line-height: 30px;
        font-size: 21px;
    }

    .col-md-6.text-left.pre_header p {
        line-height: 10px;
    }
</style>

<div class="content-wrapper hide-save">
    <div class="container hide-save">
        <div class="row patient_section">
            <div class="col-md-10 m-auto mt-50">
                <div class="box add_area">
                    <div class="box-body">
                        <div class="" id="print_area">

                            <div class="prescription_headers">
                                <div class="row">
                                    <div class="col-md-6 text-left pre_header printhl">
                                        <h3>Dr. {{ucwords($data->doctors['first_name'])}}</h3>
                                        <p>{{$data->doctors['address']}}</p>
                                        <p>{{$data->doctors['city']}}</p>
                                        <p>{{$data->doctors['mobile_no']}}</p>
                                    </div>
                                    <div class="col-md-6 text-right printhl">
                                        <h4 class="mb-0">{{ucwords($data->doctors['clinic'][0]['clinic_name'])}}</h4>
                                        <p class="mb-0">{{ucwords($data->doctors['clinic'][0]['address'])}}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="print_section">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="top_status">
                                            <div class="left_tops">
                                                {{ucwords($data->patients->patient_name)}}<br />
                                                {{($data->patients->gender==1)?'M':(($data->patients->gender==2)?'F':'')}}<br />
                                                {{$data->patients->mobile_no}}
                                            </div>
                                            <div class="right_top">
                                                <ul>
                                                    <li class="top-first">
                                                        <i class="mr-5"></i>
                                                    </li>
                                                    <li class="top-mid">
                                                        <i class="mr-5"></i>
                                                    </li>
                                                    <li class="top-last">
                                                        <i class="mr-5"></i>
                                                        {{date('M d,Y',strtotime($data->date))}}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="prescription_body">
                                        <div class="left_pres_side" style="width: 50%;">
                                            <div class="left_prescription">
                                                <div class="single_left">
                                                    <div class="left_p_header left">
                                                        <h4>Complaints</h4>
                                                    </div>
                                                    <div class="left_p_details">
                                                        <ol>
                                                            @foreach(json_decode($data->complaints_row) as $sym)
                                                            <li>{{$sym->name}}
                                                                <?php
                                                                if (isset($sym->data)) {

                                                                    $str = "(";
                                                                    foreach ($sym->data as $key => $symdata) {
                                                                        if ($key != 'type' && $key != 'title' && $symdata != null)
                                                                            $str = $str . ucwords($key) . " - " . ucwords($symdata) . ",";
                                                                    }
                                                                    $str = rtrim($str, ",") . ")";
                                                                    echo $str;
                                                                }
                                                                ?>
                                                            </li>
                                                            @endforeach
                                                        </ol>
                                                    </div>
                                                </div>
                                                <div class="single_left">
                                                    <div class="left_p_header left">
                                                        <h4>Clinical Diagnosis</h4>
                                                    </div>
                                                    <div class="left_p_details">
                                                        <ol>
                                                            @foreach(json_decode($data->diagnosis_row) as $sym)
                                                            <li>{{$sym->name}}</li>
                                                            <?php
                                                            if (isset($sym->data)) {

                                                                $str = "(";
                                                                foreach ($sym->data as $key => $symdata) {
                                                                    if ($key != 'type' && $key != 'title' && $symdata != null)
                                                                        $str = $str . ucwords($key) . " - " . ucwords($symdata) . ",";
                                                                }
                                                                $str = rtrim($str, ",") . ")";
                                                                echo $str;
                                                            }
                                                            ?>
                                                            @endforeach
                                                        </ol>
                                                    </div>
                                                </div>

                                                <div class="single_left">
                                                    <div class="left_p_header left">
                                                        <h4>Advice</h4>
                                                    </div>
                                                    <div class="left_p_details">
                                                        <ol>
                                                            <p>{{$data->advices}}</p>
                                                        </ol>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="right_pres_side">
                                            <div class="right_prescription">
                                                <div class="rx_header">
                                                    <h1>&rx;</h1>
                                                </div>
                                                <div class="right_single pl-20">
                                                    @foreach($data->medication as $row)
                                                    <div class="left_p_header">
                                                        <h4 class="drug_name">{{$row->medication_name}}</h4>
                                                        <div class="second_value">
                                                            <p>{!! $row->duration !!}( {!! $row->timing !!} )
                                                                <span>{!! $row->note !!} </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    <!-- left_p_header -->

                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                    <!-- prescription_body -->

                                </div>
                                <!-- row -->
                                <div class="footer_pescript w-100 pt-4">
                                    <div class="visit_left">
                                        <p class="last_visit_pn"> {!! $data->qrcode !!} Scan QR Code to get digital prescription </p>
                                    </div>

                                    <div class="visit_right">
                                        Dr. {{$data->doctors['first_name']}}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


            </div>
        </div>
    </div>
</div>