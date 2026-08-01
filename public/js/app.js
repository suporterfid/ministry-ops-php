// Ministry Ops PHP - Frontend Interactivity & Geolocation Check-in

document.addEventListener('DOMContentLoaded', () => {
    // 1. Geolocation Check-in Logic
    const checkinBtn = document.getElementById('btn-get-location');
    const geoStatus = document.getElementById('geo-status');
    const checkinForm = document.getElementById('checkin-form');
    const inputLat = document.getElementById('input-latitude');
    const inputLng = document.getElementById('input-longitude');

    if (checkinBtn && checkinForm) {
        checkinBtn.addEventListener('click', () => {
            if (!navigator.geolocation) {
                if (geoStatus) geoStatus.innerHTML = '<span style="color:#ef4444;">Navegador não suporta Geolocalização GPS.</span>';
                return;
            }

            if (geoStatus) {
                geoStatus.innerHTML = '<span style="color:#f59e0b;">Obtendo sua localização GPS em tempo real...</span>';
            }
            checkinBtn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = Math.round(position.coords.accuracy);

                    if (inputLat) inputLat.value = lat;
                    if (inputLng) inputLng.value = lng;

                    if (geoStatus) {
                        geoStatus.innerHTML = `<span style="color:#10b981;">Localização obtida (Precisão: ${accuracy}m). Processando check-in...</span>`;
                    }

                    // Auto-submit checkin form
                    setTimeout(() => {
                        checkinForm.submit();
                    }, 500);
                },
                (error) => {
                    checkinBtn.disabled = false;
                    let msg = 'Erro ao obter localização.';
                    if (error.code === error.PERMISSION_DENIED) {
                        msg = 'Permissão de localização negada pelo usuário no navegador.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        msg = 'Sinal GPS indisponível no momento.';
                    } else if (error.code === error.TIMEOUT) {
                        msg = 'Tempo limite esgotado ao buscar sinal GPS.';
                    }

                    if (geoStatus) {
                        geoStatus.innerHTML = `<span style="color:#ef4444;">${msg}</span>`;
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }

    // 2. Modal open/close helpers
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'flex';
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    };
});
