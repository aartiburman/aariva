<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyAV1Cl6pHg-66yCTePjUPeMQLByfYt9UsE",
    authDomain: "nepoora-notification.firebaseapp.com",
    projectId: "nepoora-notification",
    storageBucket: "nepoora-notification.firebasestorage.app",
    messagingSenderId: "150894054344",
    appId: "1:150894054344:web:133e55ecce431363d58efd",
    measurementId: "G-8D0ZDBJLM4"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
</script>