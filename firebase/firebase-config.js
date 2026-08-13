import { initializeApp } from "https://www.gstatic.com/firebasejs/12.16.0/firebase-app.js";
import {
    getFirestore,
    collection,
    addDoc,
    serverTimestamp
} from "https://www.gstatic.com/firebasejs/12.16.0/firebase-firestore.js";


const firebaseConfig = {
    apiKey: "AIzaSyDkEQjHpj-ZBxjZNsdkNdfjS3epqkxTKdQ",
    authDomain: "portify-b9125.firebaseapp.com",
    databaseURL: "https://portify-b9125-default-rtdb.asia-southeast1.firebasedatabase.app/",
    projectId: "portify-b9125",
    storageBucket: "portify-b9125.firebasestorage.app",
    messagingSenderId: "911327677437",
    appId: "1:911327677437:web:995bb5d2a415debe6ae794"
};

const app = initializeApp(firebaseConfig);

const db = getFirestore(app);

export {
    db,
    collection,
    addDoc,
    serverTimestamp
};