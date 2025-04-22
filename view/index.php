<?php
include_once("../www/header.php");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style type="text/css">
        /* Custom map styling */
        #carte {
            width: 100%;
            height: 400px;
            margin: 0 auto;
            border: 2px solid var(--main-color);
            border-radius: 15px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
        }

        /* Animation on hover */
        #carte:hover {
            transform: scale(1.02);
        }

        /* Responsive styling for smaller screens */
        @media screen and (max-width: 768px) {
            #carte {
                height: 350px;
            }
        }
    </style>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize the map with a default view
            let carte = L.map('carte').setView([36.9636727, -121.9696589], 3); // General view of the stores

            // Tile layer for OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(carte);

            // List of stores with their coordinates
            let magasins = [
                { lat: 36.9636727, lon: -121.9696589, nom: "Santa Cruz Bikes" },
                { lat: 43.0014907, lon: -83.5826126, nom: "Baldwin Bikes" },
                { lat: 32.9029017, lon: -96.56388, nom: "Rowlett Bikes" }
            ];

            // Add store markers to the map
            magasins.forEach(magasin => {
                L.marker([magasin.lat, magasin.lon]).addTo(carte)
                    .bindPopup(magasin.nom);
            });

            /**
             * Fetch the user's public IP address using ipify API
             * @returns {Promise<string|null>} The user's IP address or null in case of error
             */
            function getIP() {
                return $.getJSON("https://api.ipify.org?format=json")
                    .then(data => data.ip)
                    .catch(() => null);
            }

            /**
             * Get geographical location (latitude and longitude) based on the IP address
             * @param {string} ip - The user's IP address
             * @returns {Promise<{lat: number, lon: number}|null>} The geographical location or null if unable to determine
             */
            function getGeoLocation(ip) {
                if (!ip) return Promise.resolve(null);
                let apiKey = "YOUR_API_KEY"; // Replace with your API key from apibundle.io
                return $.getJSON(`https://api.apibundle.io/v1/ip/${ip}?apikey=${apiKey}`)
                    .then(data => ({ lat: data.latitude, lon: data.longitude }))
                    .catch(() => null);
            }

            /**
             * Locate the user's position and update the map to center around it
             */
            function locateUser() {
                getIP().then(ip => {
                    if (!ip) return;

                    getGeoLocation(ip).then(location => {
                        if (location && location.lat && location.lon) {
                            // If we have a valid location from the IP, center the map there
                            let userLocation = [location.lat, location.lon];
                            carte.setView(userLocation, 13);
                            L.marker(userLocation).addTo(carte)
                                .bindPopup("You are here")  // User's location popup
                                .openPopup();
                        } else if (navigator.geolocation) {
                            // If IP geolocation fails, fall back to browser geolocation
                            navigator.geolocation.getCurrentPosition((position) => {
                                let userLocation = [position.coords.latitude, position.coords.longitude];
                                carte.setView(userLocation, 13);
                                L.marker(userLocation).addTo(carte)
                                    .bindPopup("You are here")
                                    .openPopup();
                            });
                        }
                    });
                });
            }

            // Call the locateUser function when the page is ready
            locateUser();
        });
    </script>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <!-- The map container where the Leaflet map will be rendered -->
                <div id="carte"></div>
            </div>
        </div>
    </div>
</body>
</html>
<?php include_once("../www/footer.php"); ?>
