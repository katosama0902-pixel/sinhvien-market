<?php
$content = file_get_contents('c:/laragon/www/sinhvien-market/app/views/products/detail.php');
$newBlock = <<<HTML
<!-- ─── Tích hợp Bản đồ Google Maps API ────────────────── -->
<?php if (!empty(\$p['seller_address'])): ?>
<script>
function initMap() {
    const addressQuery = <?= json_encode(\$p['seller_address'] . " Làng Đại Học Quốc Gia TP.HCM, Việt Nam") ?>;
    const defaultLocation = { lat: 10.8700, lng: 106.8030 }; // ĐHQG mặc định

    const map = new google.maps.Map(document.getElementById("osm-map"), {
        zoom: 15,
        center: defaultLocation,
        mapTypeId: "roadmap",
        styles: [
            { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] } // Giảm rối bản đồ
        ]
    });

    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: addressQuery }, (results, status) => {
        if (status === "OK" && results[0]) {
            const location = results[0].geometry.location;
            map.setCenter(location);

            const marker = new google.maps.Marker({
                map: map,
                position: location,
                animation: google.maps.Animation.DROP
            });

            const infoWindow = new google.maps.InfoWindow({
                content: "<div class='text-center fw-bold text-primary mb-1' style='font-family:inherit'>📍 Điểm giao dịch dự kiến</div><div style='font-family:inherit'>" + <?= json_encode(htmlspecialchars(\$p['seller_address'], ENT_QUOTES)) ?> + "</div>"
            });
            infoWindow.open(map, marker);

            new google.maps.Circle({
                map: map,
                center: location,
                radius: 200,
                fillColor: "#ffc107",
                fillOpacity: 0.25,
                strokeColor: "#ffc107",
                strokeWeight: 1
            });
        }
    });
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= \$_ENV['GOOGLE_MAPS_API_KEY'] ?? '' ?>&callback=initMap"></script>
<?php endif; ?>
HTML;

$content = preg_replace('/<!-- ─── Tích hợp Bản đồ Leaflet\.js \(OpenStreetMap\) ────────────────── -->.*?<\?php endif; \?>/s', $newBlock, $content);
file_put_contents('c:/laragon/www/sinhvien-market/app/views/products/detail.php', $content);
echo "Replaced safely";
