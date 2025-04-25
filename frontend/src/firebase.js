// firebase.js
import { initializeApp } from "firebase/app";
import { getMessaging } from "firebase/messaging";

const firebaseConfig = {
  apiKey: "AIzaSyDHHbtG6Ka7j_WzWECxT9VFq0eL_c65z5Q",
  authDomain: "rentease-7d991.firebaseapp.com",
  projectId: "rentease-7d991",
  storageBucket: "rentease-7d991.firebasestorage.app",
  messagingSenderId: "977313734840",
  appId: "1:977313734840:web:fdc9d74b7a672981eed44d",
  measurementId: "G-EWXVL63EMF"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export { messaging };