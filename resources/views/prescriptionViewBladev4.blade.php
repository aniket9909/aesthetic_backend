<html>

<head>
    <style>
        .container {
            margin: 3px;
            padding: 3px;
            border: 1px solid gray;
        }

        .patientContainer {
            display: flex;
            border: 1px solid rgb(136, 131, 131);
            margin: 10px;
            padding: 5px;
            justify-content: space-evenly;

        }

        /* .patientContainer td {

    padding: 5px;

}
        .patientContainer label{
    display: block;


} */

        .patientDetails {
            display: inline-block;
            margin: 8px;
            margin-bottom: 1 px;
            padding: 8px;

        }

        .patientDetails label {
            font-weight: bold;
        }

        .prescriptionDetails {
            margin: 20px;
            /* border: 1px solid rgb(9, 8, 8); */
            /* display: flex;
            justify-content: space-around; */
            height: 47%;
        }

        .prescription-body {
            display: inline-block;
            margin: 20px;
        }

        .prescriptionDataBox {
            margin: 1px;
            padding: 1px;
        }


        .prescriptionDataBox label {
            font-weight: bold;
            font-family: Arial, Helvetica, sans-serif;
            /* margin-bottom: 1px; */
            text-decoration: underline;
            font-size: 12px;
        }

        .prescriptionDataBox p {
            /* padding: 1px; */
            margin: 1px;
            display: block;
        }

        .vl {
            border-left: 1px solid rgb(0, 0, 0);
            height: 45%;
            position: absolute;
            margin-left: -3%;
        }

        .hl {
            border: 0.5px solid black;
        }

        .footer {
            margin-top: 80%;
            margin-left: 15%;
            padding: 0%;
        }

        .footersingle {
            margin-right: 10%;
            padding: 0%;
        }

        .footerdouble {
            /* margin-top: 70%;
            margin-right: 10%;
            padding: 0%; */
        }

        .footerdouble span {
            white-space: break-spaces;
        }

        .footersingle span {
            white-space: break-spaces;
        }

        .double {
            width: 50%;
            float: left;
        }

        .single {
            width: 100%;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        table label {
            font-weight: bold;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 1px;
            text-decoration: underline;
        }

        table tr,
        td,
        th {
            border: 1px solid black;
            text-align: center;
            border-collapse: collapse
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- New Prescription Design -->
        @if ($css['letter_head'])
            <div class="header">
                @if ($data['doctor']['letterhead'])

                    <img src="{{ $data['doctor']['letterhead'] }}" alt="letterheadImage" width="710" />
                @else
                    <div style="text-align: right; width:100%">
                        <div style="font-size: 25px; margin:2px 2px;"> <b>Dr.
                                {{ $data['doctor']['pharmaclient_name'] }}</b></div>
                        <div style="margin-bottom:1px">
                            @foreach ($data['doctor']['specialization'] as $specialization)
                                <span>{{ $specialization }}</span>,
                            @endforeach
                            <spanm style="margin: 0px; padding:0px;">({{ $data['doctor']['clinic_name'] }})</spanm>
                        </div>


                        <div class="docphone mb-1">
                            <i class="fa fa-phone-square" aria-hidden="true"></i>
                        </div>
                    </div>
                @endif
            </div>
        @endif
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid black;">
            <tbody>
                <tr>
                    <td style="border: none; padding: 8px; text-align: center;">
                        <b>Patient name</b><br>{{ $data['patient']['patient_name'] }}
                    </td>
                    <td style="border: none; padding: 8px; text-align: center;">
                        <b>Mobile number</b><br>{{ $data['patient']['mobile_no'] }}
                    </td>
                    <td style="border: none; padding: 8px; text-align: center;">
                        <b>Age/Gender</b><br>{{ $data['patient']['age'] }}/{{ $data['patient']['gender'] }}
                    </td>
                    <td style="border: none; padding: 8px; text-align: center;">
                        <b>Consultation date</b><br>{{ $data['rx']['date'] }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Content -->
        <div class="prescriptionDetails">
            <div class="{{ $css['prescription_template'] == 'single' ? 'single' : 'double' }}">
                @if ($data['rx']['follow_up'])
                    <div class="prescriptionDataBox">
                        <label for="">FOLLOW UP</label>
                        <div style="padding-bottom: 2px">
                            {{ $data['rx']['follow_up'] ? $data['rx']['follow_up'] : ' ' }}
                        </div>
                    </div>
                @endif

                @if ($data['rx']['vitals'])
                    <div class="prescriptionDataBox">
                        <label for="">VITALS</label>
                        @foreach ($data['rx']['vitals'] as $vital)
                            @if ($vital['vitalname'])
                                <ul>
                                    <li>
                                        <span>{{ $vital['vitalname'] }}</span>
                                        <span>{{ $vital['value'] }}</span>
                                    </li>
                                </ul>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if ($data['rx']['test_requested'])
                    <div class="prescriptionDataBox">
                        <label for="">SUGGESTED INVESTIGATIONS</label>
                        @if ($data['rx']['test_requested'])
                            @foreach ($data['rx']['test_requested'] as $test_requested)
                                <ul>
                                    <li>
                                        {{ $test_requested }}
                                    </li>
                                </ul>
                            @endforeach
                        @endif
                    </div>
                @endif

                @if ($data['rx']['advice'])
                    <div class="prescriptionDataBox">
                        <label for="">ADVICES</label>
                        @if ($data['rx']['advice'])
                            @foreach ($data['rx']['advice'] as $advice)
                                <ul>
                                    <li>
                                        {{ $advice }}
                                    </li>
                                </ul>
                            @endforeach
                        @endif
                    </div>
                @endif
                @if ($data['rx']['medical_history'])
                    <div class="prescriptionDataBox">
                        <label for="">MEDICAL HISTORY</label>
                        <div>{{ $data['rx']['medical_history'] ? $data['rx']['medical_history'] : ' ' }}</div>
                    </div>
                @endif
                @if ($data['rx']['lifestyle'])
                    <div class="prescriptionDataBox">
                        <label for="">LIFESTYLE</label>
                        <div>{{ $data['rx']['lifestyle'] ? $data['rx']['lifestyle'] : ' ' }}</div>
                    </div>
                @endif
            </div>

            <span class="{{ $css['prescription_template'] == 'double' ? 'vl' : '' }}"></span>

            <div class="{{ $css['prescription_template'] == 'single' ? 'single' : 'double' }}">
                @if ($data['rx']['diagnosis'])
                    <div class="prescriptionDataBox">
                        <label for="diagnosis">DIAGNOSIS</label>
                        @if ($data['rx']['diagnosis'])
                            @foreach ($data['rx']['diagnosis'] as $diagnosis)
                                <ul>
                                    <li>
                                        {{ $diagnosis['name'] }}
                                        <span>({{ $diagnosis['diagnosis_days'] }})</span>
                                    </li>
                                </ul>
                            @endforeach
                        @endif
                    </div>
                @endif
                @if ($data['rx']['symptoms'])
                    <div class="prescriptionDataBox">
                        <label for="">SYMPTOMS</label>
                        @if ($data['rx']['symptoms'])
                            @foreach ($data['rx']['symptoms'] as $symptoms)
                                <ul>
                                    <li>
                                        {{ $symptoms['name'] }}
                                        @if ($symptoms['symptoms_days'] || $symptoms['severity'])
                                            <span>({{ $symptoms['symptoms_days'] }}
                                                {{ $symptoms['severity'] }})</span>
                                        @endif

                                    </li>
                                </ul>
                            @endforeach
                        @endif
                    </div>
                @endif
                @if ($css['medicine_template'] == 'single')
                    @if ($data['rx']['dataForDrugs'])
                        <div>
                            <div class='prescriptionDataBox'>
                                <label>DRUGS</label>
                                @if ($data['rx']['dataForDrugs'])

                                    @foreach ($data['rx']['dataForDrugs'] as $drugs)
                                        <ul>
                                            <li>
                                                <span>{{ $drugs['name'] }}</span>
                                                <span>{{ $drugs['duration'] }}</span>
                                                <span>({{ $drugs['timing'] }} {{ $drugs['doses'] }})</span>
                                            </li>
                                        </ul>
                                    @endforeach


                                @endif
                            </div>
                    @endif
                @elseif($css['medicine_template'] == 'table')
                    @if ($data['rx']['dataForDrugs'])
                        <div>
                            <label>DRUGS</label>
                            <table style="padding-top: 3px">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Frequency</th>
                                        <th>Duration (Days)</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['rx']['dataForDrugs'] as $drugs)
                                        <tr>
                                            <td>{{ $drugs['name'] }}</td>
                                            <td>{{ $drugs['timing'] }}</td>
                                            <td>{{ $drugs['duration'] }}</td>
                                            <td>{{ $drugs['doses'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                    @endif
                @elseif($css['medicine_template'] == 'multi')
                    @if ($data['rx']['dataForDrugs'])
                        <div>
                            <div class='prescriptionDataBox'>
                                <label>DRUGS</label>
                                @if ($data['rx']['dataForDrugs'])
                                    @foreach ($data['rx']['dataForDrugs'] as $drugs)
                                        <ul>
                                            <li>
                                                <p>{{ $drugs['name'] }}</p>
                                                <p>{{ $drugs['duration'] }}{{ $drugs['timing'] }}</p>
                                                <p>{{ $drugs['doses'] }}</p>
                                            </li>
                                        </ul>
                                    @endforeach
                                @endif
                            </div>
                    @endif
                @endif


            </div>


        </div>
        {{-- <div  style="text-align: {{ $css['footer_alignment'] }}"> --}}

        {{-- <div style=" border:2px solid black;   text-align: {{ $css['footer_alignment'] ? $css['footer_alignment'] : 'right' }}"
            class="{{ $css['prescription_template'] == 'single' ? 'footersingle' : 'footerdouble' }}">
            @if ($css['signuture'])
                <img src="{{ $data['doctor']['sign'] }}" alt="Signature Image" style="margin-right: 13%;"
                    width="70" />
            @endif
            <div style="margin-right:8%">

                <b>Dr.
                    {{ $data['doctor']['pharmaclient_name'] }}</b>
            </div>
            <div>
                <span>{{ $data['doctor']['qualification'] }}</span>
                @foreach ($data['doctor']['specialization'] as $specialization)
                    <span>{{ $specialization }}</span>,
                @endforeach
            </div>
        </div>
        <div>
                @if ($data['rx']['follow_up'])
                <img src="data:image/svg+xml;base64,' .{{ $data['rx']['qr']}} . '" />
                <p>Follow up Qr Code</p>
                @endif
       </div> --}}







        {{-- justify-content: {{ $css['footer_alignment'] ? $css['footer_alignment'] : 'flex-end' }};"  --}}

    </div>

        <div style="display: flex-container;     flex-direction: row; "
            class="{{ $css['prescription_template'] == 'single' ? 'footersingle' : 'footerdouble' }}">
            @if ($data['rx']['follow_up'])
                <div style="  flex: 50%; display: inline-block; margin-right: 250px; margin-left: 20px">
                    <img src="data:image/svg+xml;base64,{{ $data['rx']['qr'] }}" alt="QR Code" width="80"
                     />
                    <p>Follow up Qr Code</p>
                </div>
            @endif

            <div style=" flex: 50%; display: inline-block; ">
                @if ($css['signuture'])
                    <div>
                        <img src="{{ $data['doctor']['sign'] }}" alt="Signature Image" width="70" />
                    </div>
                @endif

                <div style=" display: inline-block;">
                    <b>Dr. {{ $data['doctor']['pharmaclient_name'] }}</b>
                    <div>
                        <span>{{ $data['doctor']['qualification'] }}</span>
                        @foreach ($data['doctor']['specialization'] as $specialization)
                            <span>{{ $specialization }}</span>
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>





</body>

</html>