<?php
include_once 'header.php';
include 'locations_model.php';

?>

    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?language=en&key=AIzaSyA-AB-9XZd-iQby-bNLYPFyb0pR2Qw3orw">
    </script>

    <div id="map"></div>
    <script>
        /**
         * Ceare harta noua
         */
        var infowindow;
        var map;
        var red_icon =  'http://maps.google.com/mapfiles/ms/icons/red-dot.png' ;
        var purple_icon =  'http://maps.google.com/mapfiles/ms/icons/purple-dot.png' ;
        var locations = <?php get_confirmed_locations() ?>;
        var myOptions = {
            zoom: 7,
            center: new google.maps.LatLng(46.87916, -3.32910),
            mapTypeId: 'roadmap'
        };
        map = new google.maps.Map(document.getElementById('map'), myOptions);

        /**
         * Variabila ce contine toate markerele
         */
        var markers = {};

        /**
         * Variabila ce concateneaza latitudinea si longitudinea si o returneaza
         */
        var getMarkerUniqueId= function(lat, lng) {
            return lat + '_' + lng;
        };

        /**
         * Se creeaza o instanta ce da latitudinea/longitudinea pentru google maps
         */
        var getLatLng = function(lat, lng) {
            return new google.maps.LatLng(lat, lng);
        };

        /**
         * Modul prin care alocam "modul click" pentru a realiza punerea markerelor
         */
        var addMarker = google.maps.event.addListener(map, 'click', function(e) {
            var lat = e.latLng.lat(); 
            var lng = e.latLng.lng(); 
            var markerId = getMarkerUniqueId(lat, lng); // stocarea markerelor in "obiectul markere"
            var marker = new google.maps.Marker({
                position: getLatLng(lat, lng),
                map: map,
                animation: google.maps.Animation.DROP,
                id: 'marker_' + markerId,
                html: "    <div id='info_"+markerId+"'>\n" +
                "        <table class=\"map1\">\n" +
                "            <tr>\n" +
                "                <td><a>Description:</a></td>\n" +
                "                <td><textarea  id='manual_description' placeholder='Description'></textarea></td></tr>\n" +
                "            <tr><td></td><td><input type='button' value='Save' onclick='saveData("+lat+","+lng+")'/></td></tr>\n" +
                "        </table>\n" +
                "    </div>"
            });
            markers[markerId] = marker; // stocarea markerelor in "obiectul markere"
            bindMarkerEvents(marker); // Adauga markerului eventul de click dreapta
            bindMarkerinfo(marker); 
        });

        /**
         * Asigneaza evenimentul de clic la un marker dat si invoca o functie de apel invers care va elimina markerul de pe harta.
         */
        var bindMarkerinfo = function(marker) {
            google.maps.event.addListener(marker, "click", function (point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // obtinem ID-ul marcatorului utilizând coordonatele punctului
                var marker = markers[markerId]; // gasirea markerului
                infowindow = new google.maps.InfoWindow();
                infowindow.setContent(marker.html);
                infowindow.open(map, marker);

            });
        };

        /**
         *  Asigneaza evenimentul de clic dreapta la un marker dat si invoca o functie de apel invers care va elimina markerul de pe harta.
         * 
         */
        var bindMarkerEvents = function(marker) {
            google.maps.event.addListener(marker, "rightclick", function (point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); 
                var marker = markers[markerId]; 
                removeMarker(marker, markerId); 
            });
        };


        var removeMarker = function(marker, markerId) {
            marker.setMap(null); 
            delete markers[markerId]; 
        };


        /**
         * Parcurgerea repetitiva pentru a adauga merkere in mysql
         */
        var i ; var confirmed = 0;
        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                map: map,
                icon :   locations[i][4] === '1' ?  red_icon  : purple_icon,
                html: "<div>\n" +
                "<table class=\"map1\">\n" +
                "<tr>\n" +
                "<td><a>Description:</a></td>\n" +
                "<td><textarea disabled id='manual_description' placeholder='Description'>"+locations[i][3]+"</textarea></td></tr>\n" +
                "</table>\n" +
                "</div>"
            });

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infowindow = new google.maps.InfoWindow();
                    confirmed =  locations[i][4] === '1' ?  'checked'  :  0;
                    $("#confirmed").prop(confirmed,locations[i][4]);
                    $("#id").val(locations[i][0]);
                    $("#description").val(locations[i][3]);
                    $("#form").show();
                    infowindow.setContent(marker.html);
                    infowindow.open(map, marker);
                }
            })(marker, i));
        }


        function saveData(lat,lng) {
            var description = document.getElementById('manual_description').value;
            var url = 'locations_model.php?add_location&description=' + description + '&lat=' + lat + '&lng=' + lng;
            downloadUrl(url, function(data, responseCode) {
                if (responseCode === 200  && data.length > 1) {
                    var markerId = getMarkerUniqueId(lat,lng); 
                    var manual_marker = markers[markerId]; 
                    manual_marker.setIcon(purple_icon);
                    infowindow.close();
                    infowindow.setContent("<div style=' color: purple; font-size: 25px;'> Waiting for admin confirm!!</div>");
                    infowindow.open(map, manual_marker);

                }else{
                    console.log(responseCode);
                    console.log(data);
                    infowindow.setContent("<div style='color: red; font-size: 25px;'>Inserting Errors</div>");
                }
            });
        }

        function downloadUrl(url, callback) {
            var request = window.ActiveXObject ?
                new ActiveXObject('Microsoft.XMLHTTP') :
                new XMLHttpRequest;

            request.onreadystatechange = function() {
                if (request.readyState == 4) {
                    callback(request.responseText, request.status);
                }
            };

            request.open('GET', url, true);
            request.send(null);
        }


    </script>





<?php
include_once 'footer.php';

?>