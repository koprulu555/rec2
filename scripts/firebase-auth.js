const { initializeApp, cert } = require('firebase-admin/app');
const { getAuth } = require('firebase-admin/auth');
const fs = require('fs');

// Firebase yapılandırması (RecTV uygulamasından alınacak)
const firebaseConfig = {
    apiKey: "AIzaSyEXAMPLE",
    authDomain: "rectv-app.firebaseapp.com",
    projectId: "rectv-app",
    storageBucket: "rectv-app.appspot.com",
    messagingSenderId: "123456789",
    appId: "1:123456789:web:abcdef123456"
};

// Service Account Key (GitHub Secrets'tan gelecek)
const serviceAccount = JSON.parse(process.env.FIREBASE_SERVICE_ACCOUNT);

async function getFirebaseToken() {
    try {
        initializeApp({
            credential: cert(serviceAccount),
            ...firebaseConfig
        });

        const customToken = await getAuth().createCustomToken(process.env.FIREBASE_UID);
        console.log('Firebase token alındı:', customToken);
        return customToken;
    } catch (error) {
        console.error('Firebase auth hatası:', error);
        throw error;
    }
}

module.exports = { getFirebaseToken };
