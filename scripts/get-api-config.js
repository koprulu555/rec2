const axios = require('axios');
const { getFirebaseToken } = require('./firebase-auth.js');

async function getApiConfig() {
    try {
        // 1. Firebase token al
        const firebaseToken = await getFirebaseToken();
        
        // 2. API config endpoint'ine istek yap
        const response = await axios.post('https://yourapi.com/get-config', {
            token: firebaseToken,
            deviceId: process.env.DEVICE_ID || 'default-device-id'
        }, {
            headers: {
                'User-Agent': 'RecTV/1.0',
                'Content-Type': 'application/json'
            }
        });

        // 3. API konfigürasyonunu döndür
        return response.data;
    } catch (error) {
        console.error('API config alma hatası:', error);
        throw error;
    }
}

// Config structure örneği:
// {
//     mainUrl: "https://m.prectv55.lol",
//     swKey: "4F5A9C3D9A86FA54EACEDDD635185/...",
//     userAgent: "Dart/3.7 (dart:io)",
//     referer: "https://twitter.com/"
// }

module.exports = { getApiConfig };
