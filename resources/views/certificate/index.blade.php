<!DOCTYPE html>
<html>
<head>
  <title>Fitness Certificate</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 800px;
      margin: 50px auto;
      background-color: #f9f9f9;
      padding: 20px;
      border: 1px solid #ccc;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
    }

    .title {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .subtitle {
      font-size: 18px;
      margin-bottom: 20px;
    }

    .patient-info {
      font-size: 16px;
      text-align: left;
      margin-bottom: 20px;
    }

    .doctor-info {
      font-size: 16px;
      text-align: right;
    }

    .signature {
      text-align: right;
      margin-top: 50px;
    }

    .signature img {
      max-width: 200px;
      height: auto;
    }

    .doctor-name {
      font-size: 18px;
      margin-top: 10px;
    }

    .doctor-title {
      font-size: 16px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="title">MEDICAL CERTIFICATE OF FITNESS TO RETURN</div>
      <div class="subtitle">Signature of the Government servant.....................................................</div>
      <div class="patient-info">
      <p>I, <strong>Dr. {{$doctor['first_name']}}</strong> Civil Surgeon/Staff Surgeon,
Authority Medical Attendant, of.................
Registered Medical Practitioner </p>
<p>do hereby certify that we/I have carefully examined Shri/Shrimati/ Kumari {{$certificate->patient_name}} whose signature is given above, and find that he/she recovered from his/her illness and is now fit to resume duties in Government service. We/I/also certify that before arriving at this decision, we/I have examined the original medical certificate (S) and statement(S) of the case (or certify copies thereof) on which leave was granted or extended and have taken these into consideration in arriving at our/my decision.</p>
      </div>
    </div>
    <div class="doctor-info">
      <div class="doctor-name">Dr. {{$doctor['first_name']}}</div>
      <div class="doctor-title">Medical Doctor</div>
      <div class="doctor-address">{{$doctor['address']}}</div>
    </div>
    <!-- <div class="signature">
      <img src="doctor-signature.png" alt="Doctor Signature">
    </div> -->
  </div>
</body>
</html>
