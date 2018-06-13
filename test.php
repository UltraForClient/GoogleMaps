<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
    <style type="text/css">
        html {
            height: 100%
        }

        body {
            height: 100%;
            margin: 0;
            padding: 0
        }

        #map-canvas {
            height: 100%;
            width: 80%;
            float: left;
        }

        #nav {
            height: 100%;
            width: 20%;
            float: left;
        }

    </style>
</head>
<body>
<aside id="nav">
    <ul>
        <li>
            <button id="add-point" data-toggle="false">Dodaj nowy punkt kliknieciem na mape</button>
        </li>
        <li>
            <label for="change-mode">Zmień tryb generowania mapy</label>
            <select id="change-mode">
                <option value="false">Zachowaj kolejność podróży</option>
                <option value="true">Najbardziej optymalna (pomiń kolejność)</option>
            </select>
        </li>
    </ul>
</aside>
<div id="map-canvas"></div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
<script type="text/javascript">
    const directionsDisplay = new google.maps.DirectionsRenderer();
    const directionsService = new google.maps.DirectionsService();
    const mapOptions = {
        center: new google.maps.LatLng(50.0604220, 20.2495830), //Placing the center of map to Mellon Labs, Chennai
        zoom: 6,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        draggableCursor: "crosshair"
    };
    const map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

    directionsDisplay.setMap(map);

    const origin = 'Tumlin-Osowa 59';
    const points = [
        'Samsonów',
        'Ćmińsk',
        'Kielce Warszawska 34',
        'Tumlin-Zacisze'
    ];

    calcDistance();

    function calcDistance(){
        const matrixService = new google.maps.DistanceMatrixService();
        matrixService.getDistanceMatrix({
            origins: [origin],
            destinations: points,
            travelMode: google.maps.TravelMode.DRIVING,
            avoidHighways: false,
            avoidTolls: false
        }, callback);
    }
    function callback(response, status) {
        if(status !== google.maps.DistanceMatrixStatus.OK) {
            alert("Sorry, it was an error: " + status);
        }
        else
        {
            const routes = response.rows[0];
            const sortable = [];

            for(let i= routes.elements.length-1; i>=0; i--)
            {
                const routeLength = routes.elements[i].distance.value;
                sortable.push([points[i], routeLength]);
            }

            sortable.sort(function(a,b){
                return a[1]-b[1];
            });
            const waypoints = [];
            for(let j=0;j< sortable.length-1;j++)
            {
                console.log(sortable[j][0]);
                waypoints.push({
                    location: sortable[j][0],
                    stopover: true
                });
            }
            const start = origin;
            const end = sortable[sortable.length-1][0];
            calcRoute(start,end,waypoints);
        }
    }

    function calcRoute(start,end,waypoints) {   //To calculate shortest route
        const request = {
            origin: start,
            destination: end,
            waypoints: waypoints,
            optimizeWaypoints: true,
            travelMode: google.maps.TravelMode.DRIVING
        };
        directionsService.route(request, function(response, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
                const route = response.routes[0];
                let totalDistance = 0;
                for ( let i=0;i<route.legs.length;i++)
                {
                    totalDistance+=route.legs[i].distance.value;
                }
                alert("Least total Distance for the given route is " +totalDistance/1000 + "km")
            }
        });
    }

    function addPoint(addPointDiv, map) {
        addPointDiv.addEventListener('click', () => {
            map.setOptions({draggableCursor: 'cell'});
            addPointDiv.setAttribute('data-toggle', 'true');
            addPointDiv.setAttribute('data-toggle', 'true');
        });
        google.maps.event.addListener(map, 'click', event => {
            if (addPointDiv.getAttribute('data-toggle') !== 'false') {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                map.setOptions({draggableCursor: ''});
                points.push(new window.google.maps.LatLng(lat, lng));

                this.calcDistance();

                addPointDiv.setAttribute('data-toggle', 'false');
            }
        });
    }
</script>
</body>
</html>