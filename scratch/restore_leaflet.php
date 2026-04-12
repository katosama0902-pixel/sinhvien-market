<?php
$content = file_get_contents('c:/laragon/www/sinhvien-market/app/views/products/detail.php');

$newBlock = <<<HTML
<!-- ─── Tích hợp Bản đồ Google Maps API (Đã tạm ẩn) ────────────────── -->
<?php /* if (!empty(\$p['seller_address'])): ?>
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
<?php endif; */ ?>

<!-- ─── Tích hợp Bản đồ Leaflet.js (OpenStreetMap) ────────────────── -->
<?php if (!empty(\$p['seller_address'])): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let map = L.map('osm-map').setView([10.8700, 106.8030], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let addressQuery = <?= json_encode(\$p['seller_address']) ?> + " Làng Đại Học Quốc Gia TP.HCM";
    
    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(addressQuery))
        .then(response => response.json())
        .then(data => {
            if(data && data.length > 0) {
                let lat = data[0].lat;
                let lon = data[0].lon;
                map.setView([lat, lon], 16);

                let marker = L.marker([lat, lon]).addTo(map)
                    .bindPopup("<div class='text-center fw-bold text-primary mb-1'>📍 Điểm giao dịch dự kiến</div>" + <?= json_encode(htmlspecialchars(\$p['seller_address'], ENT_QUOTES)) ?>)
                    .openPopup();
                
                L.circle([lat, lon], {
                    color: '#ffc107',
                    fillColor: '#ffc107',
                    fillOpacity: 0.25,
                    radius: 200
                }).addTo(map);
            } else {
                L.marker([10.8700, 106.8030]).addTo(map)
                    .bindPopup("Không tìm thấy địa chỉ chính xác. Kéo thả tùy ý ở khu ĐHQG.");
            }
        }).catch(err => console.log('OSM Error', err));
});
</script>
<?php endif; ?>
HTML;

$content = preg_replace('/<!-- ─── Tích hợp Bản đồ Google Maps API ────────────────── -->.*?<\?php endif; \?>/s', $newBlock, $content);
file_put_contents('c:/laragon/www/sinhvien-market/app/views/products/detail.php', $content);

// Edit profile.php to comment out Google Places Autocomplete
$profileContent = file_get_contents('c:/laragon/www/sinhvien-market/app/views/profile/edit.php');
$profileContent = str_replace('<?php if ($tab === \'general\'): ?>', '<?php /* if ($tab === \'general\'): ?>', $profileContent);
$profileContent = str_replace('callback=initAutocomplete"></script>' . "\r\n" . '<?php endif; ?>', 'callback=initAutocomplete"></script>' . "\r\n" . '<?php endif; */ ?>', $profileContent);
// Alternative replace for profile in case line endings differ
$profileContent = preg_replace('/<\?php if \(\$tab === \'general\'\): \?>.*?<\?php endif; \?>/s', "<?php /*\n\$0\n*/ ?>", $profileContent);
file_put_contents('c:/laragon/www/sinhvien-market/app/views/profile/edit.php', $profileContent);

echo "Reverted map to Leaflet and commented out Google Maps and AutoComplete safely.";
