<?php
//require_once 'vendor/autoload.php';
//
//use Ultra\SocketIO\Client;
//
//$url = '127.0.0.1:8080/test';
//$url2 = '127.0.0.1:8081/test';
//
//$client = Client::create($url);
//$client->emit('event1', 'test');
//$client->emit('event1', 'test');
//$client->emit('event1', 'test');
//
//$client2 = Client::create($url2);
//$client2->emit('event2', 'test2');
?>

<!Doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #map {
            height: 100%;
            width: 80%;
            float: left;
        }

        .nav {
            height: 100%;
            width: 20%;
            float: left;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <aside class="nav">
        <select class="findPath">
            <option value="true">Znajdź najkrotszą ścieżkę</option>
            <option value="false">Zachowaj kolejność</option>
        </select>
    </aside>
    <div style="clear:both"></div>
    <script type="application/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script>
        class GenerateMap
        {
            constructor(map, stops, directionsDisplay, directionsService) {
                this.map = map;
                this.stops = stops;
                this.directionsDisplay = directionsDisplay;
                this.directionsService = directionsService;
            }

            updateStops(newStops) {
                this.stops = newStops;
            }

            loadMap() {
                this.directionsDisplay.setMap(this.map);
            }

            fitBounds() {
                const bounds = new window.google.maps.LatLngBounds();

                this.stops.forEach(val => bounds.extend(new window.google.maps.LatLng(val.lat, val.lng)));

                this.map.fitBounds(bounds);
            }

            calcRoute() {
                const batches = [];
                const itemsPerBatch = 10;
                let itemsCounter = 0;
                let wayptsExist = this.stops.length > 0;

                while(wayptsExist) {
                    const subBatch = [];
                    let subitemsCounter = 0;

                    for (let j = itemsCounter; j < this.stops.length; j++) {
                        subitemsCounter++;
                        subBatch.push({
                            location: new window.google.maps.LatLng(this.stops[j].lat, this.stops[j].lng),
                            stopover: true
                        });

                        if (subitemsCounter === itemsPerBatch) {
                            break;
                        }
                    }

                    itemsCounter += subitemsCounter;
                    batches.push(subBatch);

                    wayptsExist = itemsCounter < this.stops.length;
                    itemsCounter--;
                }

                for (let k = 0; k < batches.length; k++) {
                    const lastIndex = batches[k].length - 1;
                    const start = batches[k][0].location;
                    const end = batches[k][lastIndex].location;

                    const waypts = batches[k];
                    waypts.splice(0, 1);
                    waypts.splice(waypts.length - 1, 1);

                    const request = {
                        origin: start,
                        destination: end,
                        waypoints: waypts,
                        travelMode: window.google.maps.TravelMode.DRIVING,
                        optimizeWaypoints: true
                    };
                    this.generateWaypants(request, k, batches)
                }
            }

            generateWaypants(request, order, batches) {
                console.log(request);

                let combinedResults;
                const unsortedResults = [{}];

                let directionsResultsReturned = 0;
                this.directionsService.route(request, (result, status) => {
                    if (status === window.google.maps.DirectionsStatus.OK) {
                        const unsortedResult = { order: order, result: result };
                        unsortedResults.push(unsortedResult);

                        directionsResultsReturned++;

                        if (directionsResultsReturned === batches.length) {
                            unsortedResults.sort((a, b) => parseFloat(a.order) - parseFloat(b.order));
                            let count = 0;
                            for(const key in unsortedResults) {
                                if (unsortedResults[key].result != null && unsortedResults.hasOwnProperty(key)) {
                                    if (count === 0) {
                                        combinedResults = unsortedResults[key].result;
                                    } else {
                                        combinedResults.routes[0].legs = combinedResults.routes[0].legs.concat(unsortedResults[key].result.routes[0].legs);
                                        combinedResults.routes[0].overview_path = combinedResults.routes[0].overview_path.concat(unsortedResults[key].result.routes[0].overview_path);

                                        combinedResults.routes[0].bounds = combinedResults.routes[0].bounds.extend(unsortedResults[key].result.routes[0].bounds.getNorthEast());
                                        combinedResults.routes[0].bounds = combinedResults.routes[0].bounds.extend(unsortedResults[key].result.routes[0].bounds.getSouthWest());
                                    }
                                    count++;
                                }
                            }
                            directionsDisplay.setDirections(combinedResults);
                        }
                    }
                });
            }

            faster() {
                const subBatches = [];
                const batches = [];
                this.stops.forEach(stop => {
                    subBatches.push({
                        location: new window.google.maps.LatLng(stop.lat, stop.lng),
                        stopover: true

                    })
                });

                batches.push(subBatches);

                const beStops = []; //this.stops.slice();
                [].forEach.call(this.stops.slice(), item => {
                    beStops.push({
                        location: new window.google.maps.LatLng(item),
                        stopover: true
                    })
                });
                const request = {
                    origin: beStops.shift().location !== undefined ? beStops.shift().location : beStops.shift(),
                    destination: beStops.pop().location !== undefined ? beStops.pop().location : beStops.pop(),
                    waypoints: beStops,
                    travelMode: window.google.maps.TravelMode.DRIVING
                };

                generateMap.generateWaypants(request, 0, batches);
            }
        }

        const stops = [
            {lat: 50.88034018835429, lng: 20.394155082645966},
            {lat: 51.88034018835429, lng: 21.394155082645966},
            {lat: 51.00034018835429, lng: 21.994155082645966},
            {lat: 52.00034018835429, lng: 22.094155082645966},

        ];

        const myOptions = {
            zoom: 4,
            center: new window.google.maps.LatLng(),
            mapTypeId: window.google.maps.MapTypeId.ROADMAP
        };

        const map = new window.google.maps.Map(document.querySelector('#map'), myOptions);

        const directionsDisplay = new window.google.maps.DirectionsRenderer();
        const directionsService = new window.google.maps.DirectionsService();

        const generateMap = new GenerateMap(map, stops.slice(), directionsDisplay, directionsService);

        generateMap.loadMap(map);
        generateMap.fitBounds(map);

        // generateMap.faster();
        generateMap.calcRoute();

        google.maps.event.addListener(map, 'click', event => {
            const lat = event.latLng.lat();
            const lng = event.latLng.lng();
            stops.push({
                lat: lat,
                lng: lng
            });
            generateMap.updateStops(stops.slice());
            // generateMap.calcRoute();
            generateMap.faster();
        });


        // const select = document.querySelector('.findPath');
        // select.addEventListener('change', e => {
        //     if(select.value === 'true') {
        //         if(stops.length > 1) {
        //             generateMap.calcRoute();
        //         }
        //
        //         google.maps.event.addListener(map, 'click', event => {
        //             const lat = event.latLng.lat();
        //             const lng = event.latLng.lng();
        //             stops.push({
        //                 lat: lat,
        //                 lng: lng
        //             });
        //
        //             generateMap.updateStops(stops);
        //             generateMap.calcRoute();
        //         });
        //     } else {
        //         console.log('false');
        //     }
        // });


        // class Test
        // {
        //     constructor(test) {
        //         this.test = test;
        //     }
        //
        //     removeFirstElement() {
        //         this.test.shift();
        //     }
        //
        //     get getTest() {
        //         return this.test;
        //     }
        //
        // }
        //
        // const arr = [1, 2, 3, 4, 5];
        //
        // const test = new Test(arr.slice());
        // test.removeFirstElement();
        //
        // console.log(arr);
        // console.log(test.getTest)
    </script>
</body>
</html>
