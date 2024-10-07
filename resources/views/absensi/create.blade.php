@extends('layouts.absensi')
@section('header')
<!-- App Header -->

<div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Absen Guys</div>
    <div class="right"></div>
</div>

<!-- * App Header -->
<style>
    .webcam-capture,
    .webcam-capture video {
        display: inline-block;
        width: 100% !important;
        margin: auto;
        height: auto !important;
        border-radius: 15px;
    }
    #map { 
        height: 180px; }
</style>
    
    <!--* Lokasi -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@endsection

@section('content')
<div class="row" style="margin-top: 70px">
   <div class="col">
    <input type="hidden" id="lokasi">
    <div class="webcam-capture"></div>
   </div>
</div>
<div class="row">
    <div class="col"></div>
    @if ($cek > 0)
    <button id="takeabsen" class="btn btn-danger btn-block">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js" ></script>
        <ion-icon name="camera-outline"></ion-icon>
        Absen Pulang
    </button>
    @else
    <button id="takeabsen" class="btn btn-primary btn-block">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js" ></script>
        <ion-icon name="camera-outline"></ion-icon>
        Absen Masuk</button>
    @endif
    
</div>
<div class="row mt-2">
    <div class="col">
        <div id="map"></div>
    </div>
</div>
    <audio id="notifikasi_in">
        <source src=" {{ asset('assets/sound/in.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_out">
        <source src=" {{ asset('assets/sound/out.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="radius">
        <source src="{{ asset('assets/sound/radius.mp3') }}" type="audio/mpeg">
</audio>
@endsection

@push('myscript')
    <script>
        var notifikasi_in = document.getElementById('notifikasi_in');
        var notifikasi_out = document.getElementById('notifikasi_out');
        var radius = document.getElementById('radius');
       
        Webcam.set({
            height: 480,
            width: 640,
            image_format: 'jpeg',
            jpeg_quality: 80,
        });
        Webcam.attach('.webcam-capture');

        var lokasi = document.getElementById('lokasi');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
            }

            function successCallback(position){
                lokasi.value = position.coords.latitude + "," + position.coords.longitude;
                var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 19);
                var lokasi_absen = "{{ $lok_absen->lokasi_absen }}";
                var lok = lokasi_absen.split(",");
                var lat_absen = lok[0];
                var long_absen = lok[1];
                var radius = "{{ $lok_absen->radius }}";
               
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);
                var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
                var circle = L.circle([lat_absen, long_absen], {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.5,
                    radius: radius
                }).addTo(map);

            }

            function errorCallback() {

            }
            let csrfToken = '{{ csrf_token() }}'; 
            $("#takeabsen").click(function(e) {
               Webcam.snap(function(uri) {
                image = uri;
               });
               var lokasi = $("#lokasi").val();
               $.ajax ({
                type: 'POST',
                url: '/absensi/store',
                data: {
                    _token: "{{ csrf_token() }}",
                    image: image,
                    lokasi: lokasi,
                },
                    cache: false,
                    success:function(respond){
                        var status = respond.split("|"); 
                        if (status[0] == "success") {
                            if (status [2] =="in") {
                                notifikasi_in.play();
                            } else {
                                notifikasi_out.play();
                            }
                            
                            Swal.fire({
                            title: 'Berhasil!',
                            text: status [1],
                            icon: 'success',
                           
                            })
                            setTimeout("location.href='/dashboard'", 3000);
                        } else {
                            if (status [2] == "radius") {
                                radius.play();
                            }
                            Swal.fire({
                            title: 'Error',
                            text: status[1],
                            icon: 'error',
                           
                            });
                        }
                        

                    }
               }); 
            }); 

    </script>
@endpush