

<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-analytics.js";
  
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyCBozqKSO6IqmmHVlRvTVQYtQV7RIgGpUY",
    authDomain: "nepoora-auth.firebaseapp.com",
    projectId: "nepoora-auth",
    storageBucket: "nepoora-auth.firebasestorage.app",
    messagingSenderId: "288333381789",
    appId: "1:288333381789:web:e8d02fd0f0f899cb729474",
    measurementId: "G-W0MZC761Q3"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
</script>